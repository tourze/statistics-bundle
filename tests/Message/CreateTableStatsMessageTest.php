<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Enum\StatType;
use StatisticsBundle\Message\CreateTableStatsMessage;
use Tourze\AsyncContracts\AsyncMessageInterface;

/**
 * @internal
 */
#[CoversClass(CreateTableStatsMessage::class)]
final class CreateTableStatsMessageTest extends TestCase
{
    private CreateTableStatsMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new CreateTableStatsMessage();
    }

    public function testImplementsAsyncMessageInterface(): void
    {
        $this->assertInstanceOf(AsyncMessageInterface::class, $this->message);
    }

    public function testSetAndGetStartTime(): void
    {
        $startTime = '2024-01-15 00:00:00';

        $this->message->setStartTime($startTime);

        $this->assertSame($startTime, $this->message->getStartTime());
    }

    public function testSetAndGetEndTime(): void
    {
        $endTime = '2024-01-15 23:59:59';

        $this->message->setEndTime($endTime);

        $this->assertSame($endTime, $this->message->getEndTime());
    }

    public function testSetAndGetStatsTable(): void
    {
        $statsTable = 'user_daily_stats';

        $this->message->setStatsTable($statsTable);

        $this->assertSame($statsTable, $this->message->getStatsTable());
    }

    public function testSetAndGetTableName(): void
    {
        $tableName = 'users';

        $this->message->setTableName($tableName);

        $this->assertSame($tableName, $this->message->getTableName());
    }

    public function testSetAndGetStatColumns(): void
    {
        $statColumns = [
            'user_count_daily_new' => ['user_id', 'count', StatType::COUNT->value],
            'user_sum_daily_total' => ['score', 'sum', StatType::SUM->value],
        ];

        $this->message->setStatColumns($statColumns);

        $this->assertSame($statColumns, $this->message->getStatColumns());
    }

    public function testSetAndGetStatColumnsWithEmptyArray(): void
    {
        $statColumns = [];

        $this->message->setStatColumns($statColumns);

        $this->assertSame($statColumns, $this->message->getStatColumns());
    }

    public function testAllPropertiesCanBeSetAndRetrieved(): void
    {
        $startTime = '2024-01-01 00:00:00';
        $endTime = '2024-01-01 23:59:59';
        $statsTable = 'orders_weekly_stats';
        $tableName = 'orders';
        $statColumns = [
            'order_count' => ['id', 'count', StatType::COUNT->value],
            'revenue_sum' => ['amount', 'sum', StatType::SUM->value],
        ];

        $this->message->setStartTime($startTime);
        $this->message->setEndTime($endTime);
        $this->message->setStatsTable($statsTable);
        $this->message->setTableName($tableName);
        $this->message->setStatColumns($statColumns);

        $this->assertSame($startTime, $this->message->getStartTime());
        $this->assertSame($endTime, $this->message->getEndTime());
        $this->assertSame($statsTable, $this->message->getStatsTable());
        $this->assertSame($tableName, $this->message->getTableName());
        $this->assertSame($statColumns, $this->message->getStatColumns());
    }

    public function testSetStartTimeWithDifferentFormats(): void
    {
        $formats = [
            '2024-01-15 10:30:45',
            '2023-12-31 23:59:59',
            '2024-02-29 12:00:00', // 闰年
        ];

        foreach ($formats as $format) {
            $this->message->setStartTime($format);
            $this->assertSame($format, $this->message->getStartTime());
        }
    }

    public function testSetEndTimeWithDifferentFormats(): void
    {
        $formats = [
            '2024-01-15 10:30:45',
            '2023-12-31 23:59:59',
            '2024-02-29 12:00:00', // 闰年
        ];

        foreach ($formats as $format) {
            $this->message->setEndTime($format);
            $this->assertSame($format, $this->message->getEndTime());
        }
    }

    public function testSetStatsTableWithDifferentTableNames(): void
    {
        $tableNames = [
            'users_daily_stats',
            'orders_weekly_stats',
            'products_monthly_stats',
            'ims_statistics_test_table',
        ];

        foreach ($tableNames as $tableName) {
            $this->message->setStatsTable($tableName);
            $this->assertSame($tableName, $this->message->getStatsTable());
        }
    }

    public function testSetStatColumnsWithComplexStructure(): void
    {
        $statColumns = [
            'user_registration_count' => [
                'user_id',
                'count',
                StatType::COUNT->value,
            ],
            'order_revenue_sum' => [
                'total_amount',
                'sum',
                StatType::SUM->value,
            ],
            'product_rating_avg' => [
                'rating',
                'avg',
                StatType::AVG->value,
            ],
        ];

        $this->message->setStatColumns($statColumns);

        $this->assertSame($statColumns, $this->message->getStatColumns());
    }
}
