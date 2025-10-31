<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use StatisticsBundle\Enum\StatType;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(StatType::class)]
final class StatTypeTest extends AbstractEnumTestCase
{
    #[TestWith([StatType::SUM, 'sum', '总和'])]
    #[TestWith([StatType::COUNT, 'count', '计数'])]
    #[TestWith([StatType::AVG, 'avg', '平均值'])]
    public function testEnumValueAndLabel(StatType $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    public function testEnumCasesCount(): void
    {
        $cases = StatType::cases();
        $this->assertCount(3, $cases);
    }

    public function testAllEnumCasesHaveStringValues(): void
    {
        $cases = StatType::cases();

        foreach ($cases as $case) {
            $this->assertNotEmpty($case->value);
        }
    }

    #[TestWith(['sum', StatType::SUM])]
    #[TestWith(['count', StatType::COUNT])]
    #[TestWith(['avg', StatType::AVG])]
    public function testFromValue(string $value, StatType $expected): void
    {
        $this->assertSame($expected, StatType::from($value));
    }

    #[TestWith(['invalid_value'])]
    #[TestWith([''])]
    #[TestWith(['SUM'])]
    #[TestWith(['Count'])]
    #[TestWith(['average'])]
    public function testFromValueWithInvalidValueShouldThrowException(string $invalidValue): void
    {
        $this->expectException(\ValueError::class);
        StatType::from($invalidValue);
    }

    public function testTryFromValueWithValidValues(): void
    {
        $this->assertSame(StatType::SUM, StatType::tryFrom('sum'));
        $this->assertSame(StatType::COUNT, StatType::tryFrom('count'));
        $this->assertSame(StatType::AVG, StatType::tryFrom('avg'));
    }

    public function testTryFromValueWithInvalidValue(): void
    {
        $this->assertNull(StatType::tryFrom('invalid_value'));
        $this->assertNull(StatType::tryFrom(''));
        $this->assertNull(StatType::tryFrom('SUM'));
        $this->assertNull(StatType::tryFrom('Count'));
        $this->assertNull(StatType::tryFrom('average'));
    }

    public function testEnumUniqueValues(): void
    {
        $cases = StatType::cases();
        $values = array_map(fn ($case) => $case->value, $cases);

        $this->assertSame(count($values), count(array_unique($values)));
    }

    public function testToArray(): void
    {
        $expected = [
            'value' => 'sum',
            'label' => '总和',
        ];
        $this->assertEquals($expected, StatType::SUM->toArray());

        $expected = [
            'value' => 'count',
            'label' => '计数',
        ];
        $this->assertEquals($expected, StatType::COUNT->toArray());

        $expected = [
            'value' => 'avg',
            'label' => '平均值',
        ];
        $this->assertEquals($expected, StatType::AVG->toArray());
    }

    public function testLabelUniqueness(): void
    {
        $cases = StatType::cases();
        $labels = array_map(fn ($case) => $case->getLabel(), $cases);

        $this->assertSame(count($labels), count(array_unique($labels)), 'All enum labels must be unique');
    }
}
