<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Metric;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Metric\MetricProviderInterface;

class MockMetricProviderTest extends TestCase
{
    public function test_mockMetricProviderImplementsInterface(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertInstanceOf(MetricProviderInterface::class, $provider);
    }

    public function test_getMetricId(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame('test_metric', $provider->getMetricId());
    }

    public function test_getMetricName(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame('Test Metric', $provider->getMetricName());
    }

    public function test_getMetricDescription(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame('A test metric for unit testing', $provider->getMetricDescription());
    }

    public function test_getMetricUnit(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame('count', $provider->getMetricUnit());
    }

    public function test_getCategory(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame('test_category', $provider->getCategory());
    }

    public function test_getCategoryOrder(): void
    {
        $provider = new MockMetricProvider();
        
        $this->assertSame(1, $provider->getCategoryOrder());
    }

    public function test_getMetricValue(): void
    {
        $provider = new MockMetricProvider();
        $date = CarbonImmutable::parse('2024-01-15');
        
        $value = $provider->getMetricValue($date);
        
        $this->assertIsNumeric($value);
        $this->assertGreaterThanOrEqual(0, $value);
    }

    public function test_getMetricValue_withDifferentDates(): void
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

    public function test_serviceTagConstant(): void
    {
        $this->assertSame('statistics.metric', MetricProviderInterface::SERVICE_TAG);
    }
}

/**
 * Mock 实现用于测试
 */
class MockMetricProvider implements MetricProviderInterface
{
    public function getMetricId(): string
    {
        return 'test_metric';
    }

    public function getMetricName(): string
    {
        return 'Test Metric';
    }

    public function getMetricDescription(): string
    {
        return 'A test metric for unit testing';
    }

    public function getMetricUnit(): string
    {
        return 'count';
    }

    public function getCategory(): string
    {
        return 'test_category';
    }

    public function getCategoryOrder(): int
    {
        return 1;
    }

    public function getMetricValue(CarbonImmutable $date): mixed
    {
        // 返回基于日期的模拟值
        return $date->dayOfYear;
    }
} 