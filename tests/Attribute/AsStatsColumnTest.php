<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Attribute\AsStatsColumn;
use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Enum\StatType;

/**
 * @internal
 */
#[CoversClass(AsStatsColumn::class)]
final class AsStatsColumnTest extends TestCase
{
    public function testConstructWithAllParameters(): void
    {
        $attribute = new AsStatsColumn(
            StatTimeDimension::DAILY_NEW,
            StatType::COUNT,
            'Test Title',
            'test_name'
        );

        $this->assertSame(StatTimeDimension::DAILY_NEW, $attribute->timeDimension);
        $this->assertSame(StatType::COUNT, $attribute->statsType);
        $this->assertSame('Test Title', $attribute->title);
        $this->assertSame('test_name', $attribute->name);
    }

    public function testConstructWithNullName(): void
    {
        $attribute = new AsStatsColumn(
            StatTimeDimension::WEEKLY_NEW,
            StatType::SUM,
            'Weekly Sum'
        );

        $this->assertSame(StatTimeDimension::WEEKLY_NEW, $attribute->timeDimension);
        $this->assertSame(StatType::SUM, $attribute->statsType);
        $this->assertSame('Weekly Sum', $attribute->title);
        $this->assertNull($attribute->name);
    }

    public function testConstructWithDifferentTimeDimensions(): void
    {
        $dimensions = [
            StatTimeDimension::DAILY_NEW,
            StatTimeDimension::DAILY_TOTAL,
            StatTimeDimension::WEEKLY_NEW,
            StatTimeDimension::WEEKLY_TOTAL,
            StatTimeDimension::MONTHLY_NEW,
            StatTimeDimension::MONTHLY_TOTAL,
        ];

        foreach ($dimensions as $dimension) {
            $attribute = new AsStatsColumn(
                $dimension,
                StatType::COUNT,
                'Test'
            );

            $this->assertSame($dimension, $attribute->timeDimension);
        }
    }

    public function testConstructWithDifferentStatTypes(): void
    {
        $types = [
            StatType::SUM,
            StatType::COUNT,
            StatType::AVG,
        ];

        foreach ($types as $type) {
            $attribute = new AsStatsColumn(
                StatTimeDimension::DAILY_NEW,
                $type,
                'Test'
            );

            $this->assertSame($type, $attribute->statsType);
        }
    }

    public function testConstructWithEmptyTitle(): void
    {
        $attribute = new AsStatsColumn(
            StatTimeDimension::MONTHLY_NEW,
            StatType::AVG,
            ''
        );

        $this->assertSame('', $attribute->title);
    }

    public function testConstructWithEmptyName(): void
    {
        $attribute = new AsStatsColumn(
            StatTimeDimension::MONTHLY_NEW,
            StatType::AVG,
            'Test',
            ''
        );

        $this->assertSame('', $attribute->name);
    }
}
