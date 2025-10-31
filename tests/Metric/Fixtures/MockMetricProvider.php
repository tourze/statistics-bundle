<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Metric\Fixtures;

use Carbon\CarbonImmutable;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * Mock 实现用于测试
 *
 * @internal
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
