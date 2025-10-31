<?php

namespace StatisticsBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use StatisticsBundle\Attribute\AsStatsColumn;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Enum\StatType;
use StatisticsBundle\Message\CreateTableStatsMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\DoctrineHelper\ReflectionHelper;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '10 * * * *')]
#[AsCronTask(expression: '59 23 * * *')]
#[AsCommand(name: self::NAME, description: '定期统计表数据')]
class StatsTableCommand extends LockableCommand
{
    public const NAME = 'app:stats-table';

    private Inflector $inflector;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * @param string[] $columns
     */
    private function createStatTable(string $tableName, array $columns): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($schemaManager->tablesExist([$tableName])) {
            $this->updateExistingTable($tableName, $columns, $schemaManager);
        } else {
            $this->createNewTable($tableName, $columns);
        }
    }

    /**
     * @param string[] $columns
     * @param AbstractSchemaManager<AbstractPlatform> $schemaManager
     */
    private function updateExistingTable(string $tableName, array $columns, AbstractSchemaManager $schemaManager): void
    {
        $tableDetail = $schemaManager->introspectTable($tableName);
        $this->addMissingColumns($tableName, $columns, $tableDetail);
        $this->removeUnusedColumns($tableName, $columns, $schemaManager);
        $this->ensureIndexExists($tableName, $tableDetail);
    }

    /**
     * @param string[] $columns
     */
    private function addMissingColumns(string $tableName, array $columns, Table $tableDetail): void
    {
        foreach ($columns as $columnName) {
            if (!$tableDetail->hasColumn($columnName)) {
                $sql = sprintf('ALTER TABLE %s ADD COLUMN %s DECIMAL(10,2)', $tableName, $columnName);
                $this->connection->executeStatement($sql);
            }
        }
    }

    /**
     * @param string[] $columns
     * @param AbstractSchemaManager<AbstractPlatform> $schemaManager
     */
    private function removeUnusedColumns(string $tableName, array $columns, AbstractSchemaManager $schemaManager): void
    {
        $tableDetail = $schemaManager->introspectTable($tableName);
        $existingColumns = array_keys($tableDetail->getColumns());
        $tableColumns = array_slice($existingColumns, 5);

        foreach ($tableColumns as $existingColumn) {
            if (!in_array($existingColumn, $columns, true)) {
                $sql = sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $existingColumn);
                $this->connection->executeStatement($sql);
            }
        }
    }

    private function ensureIndexExists(string $tableName, Table $tableDetail): void
    {
        if (!$tableDetail->hasIndex('start_end_time_idx')) {
            $sql = sprintf('CREATE UNIQUE INDEX start_end_time_idx ON %s (start_time, end_time)', $tableName);
            $this->connection->executeStatement($sql);
        }
    }

    /**
     * @param string[] $columns
     */
    private function createNewTable(string $tableName, array $columns): void
    {
        $schema = new Schema();
        $table = $schema->createTable($tableName);

        $this->addBaseColumns($table);
        $this->addDataColumns($table, $columns);
        $this->addTableIndexes($table);
        $this->executeTableCreation($schema);
    }

    private function addBaseColumns(Table $table): void
    {
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('create_time', 'datetime', ['notnull' => false]);
        $table->addColumn('update_time', 'datetime', ['notnull' => false]);
        $table->addColumn('start_time', 'datetime', ['notnull' => true]);
        $table->addColumn('end_time', 'datetime', ['notnull' => true]);
    }

    /**
     * @param string[] $columns
     */
    private function addDataColumns(Table $table, array $columns): void
    {
        foreach ($columns as $columnName) {
            $table->addColumn($columnName, 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => false]);
        }
    }

    private function addTableIndexes(Table $table): void
    {
        $table->addUniqueIndex(['start_time', 'end_time'], 'start_end_time_idx');
    }

    private function executeTableCreation(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $queries = $schema->toSql($platform);
        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $this->processEntityMetadata($meta, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * @param ClassMetadata<DailyReport> $meta
     */
    private function processEntityMetadata(ClassMetadata $meta, OutputInterface $output): void
    {
        $className = $meta->getName();

        if (0 === count(ReflectionHelper::getPropertyAttributes($meta->getReflectionClass(), AsStatsColumn::class))) {
            $output->writeln("{$className}不需要统计");

            return;
        }

        $columnsMaps = $this->extractStatsColumns($meta);
        if (0 === count($columnsMaps['statsProperties'])) {
            return;
        }

        $this->createStatsTablesForEntity($meta, $columnsMaps);
    }

    /**
     * @param ClassMetadata<DailyReport> $meta
     * @return array{
     *     statsProperties: \ReflectionProperty[],
     *     dailyColumns: array<string, array{string, string, string}>,
     *     weeklyColumns: array<string, array{string, string, string}>,
     *     monthlyColumns: array<string, array{string, string, string}>
     * }
     */
    private function extractStatsColumns(ClassMetadata $meta): array
    {
        $statsProperties = [];
        $dailyColumns = [];
        $weeklyColumns = [];
        $monthlyColumns = [];

        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            $statsColumns = $property->getAttributes(AsStatsColumn::class);
            if (0 === count($statsColumns)) {
                continue;
            }

            $statsProperties[] = $property;
            $categorized = $this->categorizeStatsColumns($property, $statsColumns);
            $dailyColumns = array_merge($dailyColumns, $categorized['dailyColumns']);
            $weeklyColumns = array_merge($weeklyColumns, $categorized['weeklyColumns']);
            $monthlyColumns = array_merge($monthlyColumns, $categorized['monthlyColumns']);
        }

        return [
            'statsProperties' => $statsProperties,
            'dailyColumns' => $dailyColumns,
            'weeklyColumns' => $weeklyColumns,
            'monthlyColumns' => $monthlyColumns,
        ];
    }

    /**
     * @param \ReflectionProperty $property
     * @param array<\ReflectionAttribute<AsStatsColumn>> $statsColumns
     * @return array{dailyColumns: array<string, array{string, string, string}>, weeklyColumns: array<string, array{string, string, string}>, monthlyColumns: array<string, array{string, string, string}>}
     */
    private function categorizeStatsColumns(\ReflectionProperty $property, array $statsColumns): array
    {
        $dailyColumns = [];
        $weeklyColumns = [];
        $monthlyColumns = [];

        foreach ($statsColumns as $attributeInstance) {
            $statsColumn = $attributeInstance->newInstance();
            $colName = $statsColumn->name ?? $property->getName() . '_' . $statsColumn->timeDimension->value;
            $columnData = [
                $this->inflector->tableize($property->getName()),
                $statsColumn->statsType->value,
                $statsColumn->timeDimension->value,
            ];

            if (StatTimeDimension::DAILY_NEW === $statsColumn->timeDimension || StatTimeDimension::DAILY_TOTAL === $statsColumn->timeDimension) {
                $dailyColumns[$colName] = $columnData;
            }

            if (StatTimeDimension::WEEKLY_NEW === $statsColumn->timeDimension || StatTimeDimension::WEEKLY_TOTAL === $statsColumn->timeDimension) {
                $weeklyColumns[$colName] = $columnData;
            }

            if (StatTimeDimension::MONTHLY_NEW === $statsColumn->timeDimension || StatTimeDimension::MONTHLY_TOTAL === $statsColumn->timeDimension) {
                $monthlyColumns[$colName] = $columnData;
            }
        }

        return [
            'dailyColumns' => $dailyColumns,
            'weeklyColumns' => $weeklyColumns,
            'monthlyColumns' => $monthlyColumns,
        ];
    }

    /**
     * @param ClassMetadata<DailyReport> $meta
     * @param array{
     *     dailyColumns: array<string, array{string, string, string}>,
     *     weeklyColumns: array<string, array{string, string, string}>,
     *     monthlyColumns: array<string, array{string, string, string}>
     * } $columnsMaps
     */
    private function createStatsTablesForEntity($meta, array $columnsMaps): void
    {
        $tableName = $meta->getTableName();
        $map = [
            $tableName . StatTimeDimension::DAILY_NEW->getTableNameSuffix() => $columnsMaps['dailyColumns'],
            $tableName . StatTimeDimension::WEEKLY_NEW->getTableNameSuffix() => $columnsMaps['weeklyColumns'],
            $tableName . StatTimeDimension::MONTHLY_NEW->getTableNameSuffix() => $columnsMaps['monthlyColumns'],
        ];

        foreach ($map as $statsTable => $statColumns) {
            if (0 === count($statColumns)) {
                continue;
            }

            $this->createStatTable($statsTable, array_keys($statColumns));
            $this->dispatchStatsMessage($statsTable, $statColumns, $tableName);
        }
    }

    /**
     * @param array<string, array{string, string, string}> $statColumns
     */
    private function dispatchStatsMessage(string $statsTable, array $statColumns, string $tableName): void
    {
        $carbon = CarbonImmutable::now();
        if ($carbon->hour <= 1) {
            $carbon = CarbonImmutable::yesterday();
        }

        [$startTime, $endTime] = $this->calculateTimeRange($statsTable, $carbon);

        $message = new CreateTableStatsMessage();
        $message->setStartTime($startTime->format('Y-m-d H:i:s'));
        $message->setEndTime($endTime->format('Y-m-d H:i:s'));
        $message->setStatsTable($statsTable);
        $message->setStatColumns($statColumns);
        $message->setTableName($tableName);
        $this->messageBus->dispatch($message);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function calculateTimeRange(string $statsTable, CarbonImmutable $carbon): array
    {
        $startTime = $carbon->startOfDay();
        $endTime = $startTime->endOfDay();

        if (str_ends_with($statsTable, StatTimeDimension::WEEKLY_NEW->getTableNameSuffix())) {
            $startTime = $carbon->startOfWeek();
            $endTime = $startTime->endOfWeek();
        } elseif (str_ends_with($statsTable, StatTimeDimension::MONTHLY_NEW->getTableNameSuffix())) {
            $startTime = $carbon->startOfMonth();
            $endTime = $startTime->endOfMonth();
        }

        return [$startTime, $endTime];
    }
}
