<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\Message\CreateTableStatsMessage;
use Tourze\AsyncContracts\AsyncMessageInterface;

class CreateTableStatsMessageTest extends TestCase
{
    private CreateTableStatsMessage $message;

    protected function setUp(): void
    {
        $this->message = new CreateTableStatsMessage();
    }

    public function test_implementsAsyncMessageInterface(): void
    {
        $this->assertInstanceOf(AsyncMessageInterface::class, $this->message);
    }

    public function test_setAndGetStartTime(): void
    {
        $startTime = '2024-01-15 00:00:00';
        
        $this->message->setStartTime($startTime);
        
        $this->assertSame($startTime, $this->message->getStartTime());
    }

    public function test_setAndGetEndTime(): void
    {
        $endTime = '2024-01-15 23:59:59';
        
        $this->message->setEndTime($endTime);
        
        $this->assertSame($endTime, $this->message->getEndTime());
    }

    public function test_setAndGetStatsTable(): void
    {
        $statsTable = 'user_daily_stats';
        
        $this->message->setStatsTable($statsTable);
        
        $this->assertSame($statsTable, $this->message->getStatsTable());
    }

    public function test_setAndGetTableName(): void
    {
        $tableName = 'users';
        
        $this->message->setTableName($tableName);
        
        $this->assertSame($tableName, $this->message->getTableName());
    }

    public function test_setAndGetStatColumns(): void
    {
        $statColumns = [
            'user_count_daily_new' => ['user_id', 'count', 'daily_new'],
            'user_sum_daily_total' => ['score', 'sum', 'daily_total']
        ];
        
        $this->message->setStatColumns($statColumns);
        
        $this->assertSame($statColumns, $this->message->getStatColumns());
    }

    public function test_setAndGetStatColumns_withEmptyArray(): void
    {
        $statColumns = [];
        
        $this->message->setStatColumns($statColumns);
        
        $this->assertSame($statColumns, $this->message->getStatColumns());
    }

    public function test_allPropertiesCanBeSetAndRetrieved(): void
    {
        $startTime = '2024-01-01 00:00:00';
        $endTime = '2024-01-01 23:59:59';
        $statsTable = 'orders_weekly_stats';
        $tableName = 'orders';
        $statColumns = [
            'order_count' => ['id', 'count', 'weekly_new'],
            'revenue_sum' => ['amount', 'sum', 'weekly_total']
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

    public function test_setStartTime_withDifferentFormats(): void
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

    public function test_setEndTime_withDifferentFormats(): void
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

    public function test_setStatsTable_withDifferentTableNames(): void
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

    public function test_setStatColumns_withComplexStructure(): void
    {
        $statColumns = [
            'user_registration_count' => [
                'user_id',
                'count', 
                'daily_new',
                'extra_info' => 'test'
            ],
            'order_revenue_sum' => [
                'total_amount',
                'sum',
                'monthly_total'
            ],
            'product_rating_avg' => [
                'rating',
                'avg',
                'weekly_total'
            ]
        ];
        
        $this->message->setStatColumns($statColumns);
        
        $this->assertSame($statColumns, $this->message->getStatColumns());
    }
} 