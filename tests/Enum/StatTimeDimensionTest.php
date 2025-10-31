<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use StatisticsBundle\Enum\StatTimeDimension;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(StatTimeDimension::class)]
final class StatTimeDimensionTest extends AbstractEnumTestCase
{
    #[TestWith([StatTimeDimension::DAILY_NEW, 'daily_new', '每日新增'])]
    #[TestWith([StatTimeDimension::WEEKLY_NEW, 'weekly_new', '每周新增'])]
    #[TestWith([StatTimeDimension::MONTHLY_NEW, 'monthly_new', '每月新增'])]
    #[TestWith([StatTimeDimension::DAILY_TOTAL, 'daily_total', '每日总量'])]
    #[TestWith([StatTimeDimension::WEEKLY_TOTAL, 'weekly_total', '每周总量'])]
    #[TestWith([StatTimeDimension::MONTHLY_TOTAL, 'monthly_total', '每月总量'])]
    public function testEnumValueAndLabel(StatTimeDimension $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    public function testGetTableNameSuffixDailyDimensions(): void
    {
        $this->assertSame('_daily_stats', StatTimeDimension::DAILY_NEW->getTableNameSuffix());
        $this->assertSame('_daily_stats', StatTimeDimension::DAILY_TOTAL->getTableNameSuffix());
    }

    public function testGetTableNameSuffixWeeklyDimensions(): void
    {
        $this->assertSame('_weekly_stats', StatTimeDimension::WEEKLY_NEW->getTableNameSuffix());
        $this->assertSame('_weekly_stats', StatTimeDimension::WEEKLY_TOTAL->getTableNameSuffix());
    }

    public function testGetTableNameSuffixMonthlyDimensions(): void
    {
        $this->assertSame('_monthly_stats', StatTimeDimension::MONTHLY_NEW->getTableNameSuffix());
        $this->assertSame('_monthly_stats', StatTimeDimension::MONTHLY_TOTAL->getTableNameSuffix());
    }

    public function testAllEnumCasesHaveTableSuffix(): void
    {
        $cases = StatTimeDimension::cases();

        $this->assertCount(6, $cases);

        foreach ($cases as $case) {
            $suffix = $case->getTableNameSuffix();
            $this->assertStringStartsWith('_', $suffix);
            $this->assertStringEndsWith('_stats', $suffix);
        }
    }

    public function testEnumCasesCount(): void
    {
        $cases = StatTimeDimension::cases();
        $this->assertCount(6, $cases);
    }

    #[TestWith(['daily_new', StatTimeDimension::DAILY_NEW])]
    #[TestWith(['weekly_new', StatTimeDimension::WEEKLY_NEW])]
    #[TestWith(['monthly_new', StatTimeDimension::MONTHLY_NEW])]
    #[TestWith(['daily_total', StatTimeDimension::DAILY_TOTAL])]
    #[TestWith(['weekly_total', StatTimeDimension::WEEKLY_TOTAL])]
    #[TestWith(['monthly_total', StatTimeDimension::MONTHLY_TOTAL])]
    public function testFromValue(string $value, StatTimeDimension $expected): void
    {
        $this->assertSame($expected, StatTimeDimension::from($value));
    }

    #[TestWith(['invalid_value'])]
    #[TestWith([''])]
    #[TestWith(['daily'])]
    #[TestWith(['DAILY_NEW'])]
    public function testFromValueWithInvalidValueShouldThrowException(string $invalidValue): void
    {
        $this->expectException(\ValueError::class);
        StatTimeDimension::from($invalidValue);
    }

    public function testTryFromValueWithValidValues(): void
    {
        $this->assertSame(StatTimeDimension::DAILY_NEW, StatTimeDimension::tryFrom('daily_new'));
        $this->assertSame(StatTimeDimension::WEEKLY_NEW, StatTimeDimension::tryFrom('weekly_new'));
        $this->assertSame(StatTimeDimension::MONTHLY_NEW, StatTimeDimension::tryFrom('monthly_new'));
    }

    public function testTryFromValueWithInvalidValue(): void
    {
        $this->assertNull(StatTimeDimension::tryFrom('invalid_value'));
        $this->assertNull(StatTimeDimension::tryFrom(''));
        $this->assertNull(StatTimeDimension::tryFrom('daily'));
    }

    public function testToArray(): void
    {
        $expected = [
            'value' => 'daily_new',
            'label' => '每日新增',
        ];
        $this->assertEquals($expected, StatTimeDimension::DAILY_NEW->toArray());

        $expected = [
            'value' => 'weekly_total',
            'label' => '每周总量',
        ];
        $this->assertEquals($expected, StatTimeDimension::WEEKLY_TOTAL->toArray());

        $expected = [
            'value' => 'monthly_new',
            'label' => '每月新增',
        ];
        $this->assertEquals($expected, StatTimeDimension::MONTHLY_NEW->toArray());

        $expected = [
            'value' => 'daily_total',
            'label' => '每日总量',
        ];
        $this->assertEquals($expected, StatTimeDimension::DAILY_TOTAL->toArray());

        $expected = [
            'value' => 'weekly_new',
            'label' => '每周新增',
        ];
        $this->assertEquals($expected, StatTimeDimension::WEEKLY_NEW->toArray());

        $expected = [
            'value' => 'monthly_total',
            'label' => '每月总量',
        ];
        $this->assertEquals($expected, StatTimeDimension::MONTHLY_TOTAL->toArray());
    }

    public function testValueUniqueness(): void
    {
        $cases = StatTimeDimension::cases();
        $values = array_map(fn ($case) => $case->value, $cases);

        $this->assertSame(count($values), count(array_unique($values)), 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $cases = StatTimeDimension::cases();
        $labels = array_map(fn ($case) => $case->getLabel(), $cases);

        $this->assertSame(count($labels), count(array_unique($labels)), 'All enum labels must be unique');
    }
}
