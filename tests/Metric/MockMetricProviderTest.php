<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Metric;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Metric\MetricProviderInterface;
use StatisticsBundle\Tests\Metric\Fixtures\MockMetricProvider;

/**
 * @internal
 */
#[CoversClass(MockMetricProvider::class)]
final class MockMetricProviderTest extends TestCase
{
    public function testMockMetricProviderImplementsInterface(): void
    {
        $provider = new MockMetricProvider();

        $this->assertInstanceOf(MetricProviderInterface::class, $provider);
    }

    public function testGetMetricId(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame('test_metric', $provider->getMetricId());
    }

    public function testGetMetricName(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame('Test Metric', $provider->getMetricName());
    }

    public function testGetMetricDescription(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame('A test metric for unit testing', $provider->getMetricDescription());
    }

    public function testGetMetricUnit(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame('count', $provider->getMetricUnit());
    }

    public function testGetCategory(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame('test_category', $provider->getCategory());
    }

    public function testGetCategoryOrder(): void
    {
        $provider = new MockMetricProvider();

        $this->assertSame(1, $provider->getCategoryOrder());
    }

    public function testGetMetricValue(): void
    {
        $provider = new MockMetricProvider();
        $date = CarbonImmutable::parse('2024-01-15');

        $value = $provider->getMetricValue($date);

        $this->assertIsNumeric($value);
        $this->assertGreaterThanOrEqual(0, $value);
    }

    public function testGetMetricValueWithDifferentDates(): void
    {
        $provider = new MockMetricProvider();

        $dates = [
            CarbonImmutable::parse('2024-01-01'),
            CarbonImmutable::parse('2024-06-15'),
            CarbonImmutable::parse('2024-12-31'),
        ];

        foreach ($dates as $date) {
            $value = $provider->getMetricValue($date);
            $this->assertIsNumeric($value);
        }
    }

    public function testServiceTagConstant(): void
    {
        $this->assertSame('statistics.metric', MetricProviderInterface::SERVICE_TAG);
    }
}
