<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\Enum\StatTimeDimension;

class StatTimeDimensionTest extends TestCase
{
    public function test_enumValues(): void
    {
        $this->assertSame('daily_new', StatTimeDimension::DAILY_NEW->value);
        $this->assertSame('weekly_new', StatTimeDimension::WEEKLY_NEW->value);
        $this->assertSame('monthly_new', StatTimeDimension::MONTHLY_NEW->value);
        $this->assertSame('daily_total', StatTimeDimension::DAILY_TOTAL->value);
        $this->assertSame('weekly_total', StatTimeDimension::WEEKLY_TOTAL->value);
        $this->assertSame('monthly_total', StatTimeDimension::MONTHLY_TOTAL->value);
    }

    public function test_getTableNameSuffix_dailyDimensions(): void
    {
        $this->assertSame('_daily_stats', StatTimeDimension::DAILY_NEW->getTableNameSuffix());
        $this->assertSame('_daily_stats', StatTimeDimension::DAILY_TOTAL->getTableNameSuffix());
    }

    public function test_getTableNameSuffix_weeklyDimensions(): void
    {
        $this->assertSame('_weekly_stats', StatTimeDimension::WEEKLY_NEW->getTableNameSuffix());
        $this->assertSame('_weekly_stats', StatTimeDimension::WEEKLY_TOTAL->getTableNameSuffix());
    }

    public function test_getTableNameSuffix_monthlyDimensions(): void
    {
        $this->assertSame('_monthly_stats', StatTimeDimension::MONTHLY_NEW->getTableNameSuffix());
        $this->assertSame('_monthly_stats', StatTimeDimension::MONTHLY_TOTAL->getTableNameSuffix());
    }

    public function test_allEnumCasesHaveTableSuffix(): void
    {
        $cases = StatTimeDimension::cases();
        
        $this->assertCount(6, $cases);
        
        foreach ($cases as $case) {
            $suffix = $case->getTableNameSuffix();
            $this->assertStringStartsWith('_', $suffix);
            $this->assertStringEndsWith('_stats', $suffix);
        }
    }

    public function test_enumCasesCount(): void
    {
        $cases = StatTimeDimension::cases();
        $this->assertCount(6, $cases);
    }

    public function test_fromValue(): void
    {
        $this->assertSame(StatTimeDimension::DAILY_NEW, StatTimeDimension::from('daily_new'));
        $this->assertSame(StatTimeDimension::WEEKLY_NEW, StatTimeDimension::from('weekly_new'));
        $this->assertSame(StatTimeDimension::MONTHLY_NEW, StatTimeDimension::from('monthly_new'));
        $this->assertSame(StatTimeDimension::DAILY_TOTAL, StatTimeDimension::from('daily_total'));
        $this->assertSame(StatTimeDimension::WEEKLY_TOTAL, StatTimeDimension::from('weekly_total'));
        $this->assertSame(StatTimeDimension::MONTHLY_TOTAL, StatTimeDimension::from('monthly_total'));
    }

    public function test_tryFromValue_withValidValues(): void
    {
        $this->assertSame(StatTimeDimension::DAILY_NEW, StatTimeDimension::tryFrom('daily_new'));
        $this->assertSame(StatTimeDimension::WEEKLY_NEW, StatTimeDimension::tryFrom('weekly_new'));
        $this->assertSame(StatTimeDimension::MONTHLY_NEW, StatTimeDimension::tryFrom('monthly_new'));
    }

    public function test_tryFromValue_withInvalidValue(): void
    {
        $this->assertNull(StatTimeDimension::tryFrom('invalid_value'));
        $this->assertNull(StatTimeDimension::tryFrom(''));
        $this->assertNull(StatTimeDimension::tryFrom('daily'));
    }
} 