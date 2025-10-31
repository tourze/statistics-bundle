<?php

namespace StatisticsBundle\MessageHandler;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;
use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Enum\StatType;
use StatisticsBundle\Message\CreateTableStatsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * 执行SQL插入
 */
#[AsMessageHandler]
class CreateTableStatsHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(CreateTableStatsMessage $message): void
    {
        $startTime = CarbonImmutable::parse($message->getStartTime());
        $endTime = CarbonImmutable::parse($message->getEndTime());

        foreach ($message->getStatColumns() as $columnName => [$propertyName, $statsType, $timeDimension]) {
            // Process statistics columns
            $qb = $this->connection->createQueryBuilder();
            $qb->from($message->getTableName());

            // 确保转换为枚举对象
            if (is_string($statsType)) {
                $statsType = StatType::from($statsType);
            }
            if (is_string($timeDimension)) {
                $timeDimension = StatTimeDimension::from($timeDimension);
            }

            switch ($statsType) {
                case StatType::COUNT:
                    $qb->select("COUNT(DISTINCT {$propertyName})");
                    break;
                case StatType::SUM:
                    $qb->select("COUNT({$propertyName})");
                    break;
                case StatType::AVG:
                    $qb->select("AVG({$propertyName})");
                    break;
            }

            if (in_array($timeDimension, [StatTimeDimension::DAILY_NEW, StatTimeDimension::WEEKLY_NEW, StatTimeDimension::MONTHLY_NEW], true)) {
                $qb->andWhere('create_time BETWEEN :start AND :end');
                $qb->setParameter('start', $startTime);
                $qb->setParameter('end', $endTime);
            }

            $statResult = $qb->executeQuery()->fetchOne();

            // 先查询有没有对应日期的数据
            // 有的话就update，否则insert
            $rowCount = $this->connection
                ->createQueryBuilder()
                ->select('COUNT(1)')
                ->from($message->getStatsTable())
                ->where('start_time = :start_time AND end_time = :end_time')
                ->setParameter('start_time', $startTime)
                ->setParameter('end_time', $endTime)
                ->executeQuery()
                ->fetchOne()
            ;
            if ($rowCount > 0) {
                $this->connection->update($message->getStatsTable(), [
                    $columnName => $statResult,
                    'update_time' => CarbonImmutable::now(),
                ], [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            } else {
                $this->connection->insert($message->getStatsTable(), [
                    $columnName => $statResult,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'create_time' => CarbonImmutable::now(),
                ]);
            }
        }
    }
}
