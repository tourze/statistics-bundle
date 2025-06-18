<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\Enum\StatType;

class StatTypeTest extends TestCase
{
    public function test_enumValues(): void
    {
        $this->assertSame('sum', StatType::SUM->value);
        $this->assertSame('count', StatType::COUNT->value);
        $this->assertSame('avg', StatType::AVG->value);
    }

    public function test_enumCasesCount(): void
    {
        $cases = StatType::cases();
        $this->assertCount(3, $cases);
    }

    public function test_allEnumCasesHaveStringValues(): void
    {
        $cases = StatType::cases();
        
        foreach ($cases as $case) {
            $this->assertNotEmpty($case->value);
        }
    }

    public function test_fromValue(): void
    {
        $this->assertSame(StatType::SUM, StatType::from('sum'));
        $this->assertSame(StatType::COUNT, StatType::from('count'));
        $this->assertSame(StatType::AVG, StatType::from('avg'));
    }

    public function test_tryFromValue_withValidValues(): void
    {
        $this->assertSame(StatType::SUM, StatType::tryFrom('sum'));
        $this->assertSame(StatType::COUNT, StatType::tryFrom('count'));
        $this->assertSame(StatType::AVG, StatType::tryFrom('avg'));
    }

    public function test_tryFromValue_withInvalidValue(): void
    {
        $this->assertNull(StatType::tryFrom('invalid_value'));
        $this->assertNull(StatType::tryFrom(''));
        $this->assertNull(StatType::tryFrom('SUM'));
        $this->assertNull(StatType::tryFrom('Count'));
        $this->assertNull(StatType::tryFrom('average'));
    }

    public function test_enumUniqueValues(): void
    {
        $cases = StatType::cases();
        $values = array_map(fn($case) => $case->value, $cases);
        
        $this->assertSame(count($values), count(array_unique($values)));
    }
} 