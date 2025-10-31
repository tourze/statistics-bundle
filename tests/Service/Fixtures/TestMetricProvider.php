<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Service\Fixtures;

use Carbon\CarbonImmutable;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * 测试用的指标提供者实现
 *
 * @internal
 */
class TestMetricProvider implements MetricProviderInterface
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
        return $date->dayOfYear;
    }
}
