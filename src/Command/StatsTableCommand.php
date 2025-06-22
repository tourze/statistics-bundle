<?php

namespace StatisticsBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use StatisticsBundle\Attribute\AsStatsColumn;
use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Message\CreateTableStatsMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\DoctrineHelper\ReflectionHelper;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask('10 * * * *')]
#[AsCronTask('59 23 * * *')]
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

    private function createStatTable(string $tableName, array $columns): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($schemaManager->tablesExist([$tableName])) {
            // 获取当前表的详细信息
            $tableDetail = $schemaManager->introspectTable($tableName);

            // 检查并更新表结构
            //$fromSchema = $schemaManager->introspectSchema();
            //$fromSchema->getTable($tableName);

            // 检查字段是否存在或需要更新
            foreach ($columns as $columnName) {
                if (!$tableDetail->hasColumn($columnName)) {
                    // 添加缺失的列
                    $sql = sprintf('ALTER TABLE %s ADD COLUMN %s DECIMAL(10,2)', $tableName, $columnName);
                    $this->connection->executeStatement($sql);
                }
            }
            $tableDetail = $schemaManager->introspectTable($tableName);

            // 获取表中已有的列名
            $existingColumns = array_keys($tableDetail->getColumns());
            $tableColumns = array_slice($existingColumns, 5);
            // 检查是否需要删除字段
            foreach ($tableColumns as $existingColumn) {
                if (!in_array($existingColumn, $columns)) {
                    // 删除不存在于$columns中的列
                    $sql = sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $existingColumn);
                    $this->connection->executeStatement($sql);
                }
            }

            // 检查索引是否存在，如果不存在则添加
            if (!$tableDetail->hasIndex('start_end_time_idx')) {
                $sql = sprintf('CREATE UNIQUE INDEX start_end_time_idx ON %s (start_time, end_time)', $tableName);
                $this->connection->executeStatement($sql);
            }
        } else {
            // 创建新表
            $schema = new Schema();
            $table = $schema->createTable($tableName);

            // 添加必需的字段
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('create_time', 'datetime', ['notnull' => false]);
            $table->addColumn('update_time', 'datetime', ['notnull' => false]);
            $table->addColumn('start_time', 'datetime', ['notnull' => true]);
            $table->addColumn('end_time', 'datetime', ['notnull' => true]);

            // 添加传入的列
            foreach ($columns as $columnName) {
                $table->addColumn($columnName, 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => false]);
            }

            // 添加默认的索引和唯一索引
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['start_time', 'end_time'], 'start_end_time_idx');

            // 获取并执行创建表的SQL
            $platform = $this->connection->getDatabasePlatform();
            $queries = $schema->toSql($platform);  // 获取所有需要执行的SQL语句
            foreach ($queries as $query) {
                $this->connection->executeStatement($query);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $className = $meta->getName();
            if (empty(ReflectionHelper::getPropertyAttributes($meta->getReflectionClass(), AsStatsColumn::class))) {
                $output->writeln("{$className}不需要统计");
                continue;
            }

            // 先查出所有可能的统计字段
            $statsProperties = [];
            $dailyColumns = [];
            $weeklyColumns = [];
            $monthlyColumns = [];
            foreach ($meta->getReflectionClass()->getProperties() as $property) {
                $statsColumns = $property->getAttributes(AsStatsColumn::class);
                if (empty($statsColumns)) {
                    continue;
                }
                $statsProperties[] = $statsProperties;

                foreach ($statsColumns as $statsColumn) {
                    $statsColumn = $statsColumn->newInstance();
                    /** @var AsStatsColumn $statsColumn */
                    $colName = $statsColumn->name ?? $property->getName() . '_' . $statsColumn->timeDimension->value;
                    if (StatTimeDimension::DAILY_NEW === $statsColumn->timeDimension || StatTimeDimension::DAILY_TOTAL === $statsColumn->timeDimension) {
                        $dailyColumns[$colName] = [
                            $this->inflector->tableize($property->getName()),
                            $statsColumn->statsType,
                            $statsColumn->timeDimension,
                        ];
                    }
                    if (StatTimeDimension::WEEKLY_NEW === $statsColumn->timeDimension || StatTimeDimension::WEEKLY_TOTAL === $statsColumn->timeDimension) {
                        $weeklyColumns[$colName] = [
                            $this->inflector->tableize($property->getName()),
                            $statsColumn->statsType,
                            $statsColumn->timeDimension,
                        ];
                    }
                    if (StatTimeDimension::MONTHLY_NEW === $statsColumn->timeDimension || StatTimeDimension::MONTHLY_TOTAL === $statsColumn->timeDimension) {
                        $monthlyColumns[$colName] = [
                            $this->inflector->tableize($property->getName()),
                            $statsColumn->statsType,
                            $statsColumn->timeDimension,
                        ];
                    }
                }
            }
            if (empty($statsProperties)) {
                continue;
            }

            $tableName = $meta->getTableName();
            $map = [
                $tableName . StatTimeDimension::DAILY_NEW->getTableNameSuffix() => $dailyColumns,
                $tableName . StatTimeDimension::WEEKLY_NEW->getTableNameSuffix() => $weeklyColumns,
                $tableName . StatTimeDimension::MONTHLY_NEW->getTableNameSuffix() => $monthlyColumns,
            ];

            foreach ($map as $statsTable => $statColumns) {
                if (empty($statColumns)) {
                    continue;
                }
                $this->createStatTable($statsTable, array_keys($statColumns));

                $carbon = CarbonImmutable::now();
                // 凌晨0点到1点，我们继续统计昨天，这样子数据会准确点
                if ($carbon->hour <= 1) {
                    $carbon = CarbonImmutable::yesterday();
                }

                $startTime = $carbon->startOfDay();
                $endTime = $startTime->endOfDay();

                if (str_ends_with($statsTable, StatTimeDimension::WEEKLY_NEW->getTableNameSuffix())) {
                    $startTime = $carbon->startOfWeek();
                    $endTime = $startTime->endOfWeek();
                }
                if (str_ends_with($statsTable, StatTimeDimension::MONTHLY_NEW->getTableNameSuffix())) {
                    $startTime = $carbon->startOfMonth();
                    $endTime = $startTime->endOfMonth();
                }

                $message = new CreateTableStatsMessage();
                $message->setStartTime($startTime->format('Y-m-d H:i:s'));
                $message->setEndTime($endTime->format('Y-m-d H:i:s'));
                $message->setStatsTable($statsTable);
                $message->setStatColumns($statColumns);
                $message->setTableName($tableName);
                $this->messageBus->dispatch($message);
            }
        }

        return Command::SUCCESS;
    }
}
