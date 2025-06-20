<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;

class DailyMetricTest extends TestCase
{
    private DailyMetric $metric;

    protected function setUp(): void
    {
        $this->metric = new DailyMetric();
    }

    public function test_initialValues(): void
    {
        $this->assertSame(0, $this->metric->getId());
        $this->assertSame(0.0, $this->metric->getValue());
        $this->assertNull($this->metric->getCreateTime());
        $this->assertNull($this->metric->getUpdateTime());
        $this->assertNull($this->metric->getMetricUnit());
        $this->assertNull($this->metric->getCategory());
    }

    public function test_setAndGetId(): void
    {
        $this->metric->setId(123);
        $this->assertSame(123, $this->metric->getId());

        $this->metric->setId(null);
        $this->assertNull($this->metric->getId());
    }

    public function test_setAndGetMetricId(): void
    {
        $metricId = 'user_registration_count';

        $this->metric->setMetricId($metricId);

        $this->assertSame($metricId, $this->metric->getMetricId());
        $this->assertSame($this->metric, $this->metric->setMetricId($metricId));
    }

    public function test_setAndGetMetricName(): void
    {
        $metricName = '用户注册数量';

        $this->metric->setMetricName($metricName);

        $this->assertSame($metricName, $this->metric->getMetricName());
        $this->assertSame($this->metric, $this->metric->setMetricName($metricName));
    }

    public function test_setAndGetMetricUnit(): void
    {
        $unit = '人';

        $this->metric->setMetricUnit($unit);

        $this->assertSame($unit, $this->metric->getMetricUnit());
        $this->assertSame($this->metric, $this->metric->setMetricUnit($unit));

        // 测试设置为null
        $this->metric->setMetricUnit(null);
        $this->assertNull($this->metric->getMetricUnit());
    }

    public function test_setAndGetCategory(): void
    {
        $category = '用户相关';

        $this->metric->setCategory($category);

        $this->assertSame($category, $this->metric->getCategory());
        $this->assertSame($this->metric, $this->metric->setCategory($category));

        // 测试设置为null
        $this->metric->setCategory(null);
        $this->assertNull($this->metric->getCategory());
    }

    public function test_setAndGetReport(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $this->metric->setReport($report);

        $this->assertSame($report, $this->metric->getReport());
        $this->assertSame($this->metric, $this->metric->setReport($report));
    }

    public function test_setValue_withNumericValues(): void
    {
        // 测试整数
        $this->metric->setValue(42);
        $this->assertSame(42.0, $this->metric->getValue());

        // 测试浮点数
        $this->metric->setValue(3.14159);
        $this->assertSame(3.14159, $this->metric->getValue());

        // 测试负数
        $this->metric->setValue(-5);
        $this->assertSame(-5.0, $this->metric->getValue());

        // 测试零
        $this->metric->setValue(0);
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function test_setValue_withBooleanValues(): void
    {
        $this->metric->setValue(true);
        $this->assertSame(1.0, $this->metric->getValue());

        $this->metric->setValue(false);
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function test_setValue_withStringValues(): void
    {
        // 数字字符串
        $this->metric->setValue('123');
        $this->assertSame(123.0, $this->metric->getValue());

        $this->metric->setValue('45.67');
        $this->assertSame(45.67, $this->metric->getValue());

        // 非数字字符串
        $this->metric->setValue('abc');
        $this->assertSame(0.0, $this->metric->getValue());

        // 空字符串
        $this->metric->setValue('');
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function test_setValue_withNullValue(): void
    {
        $this->metric->setValue(null);
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function test_setValue_withArrayValue(): void
    {
        $this->metric->setValue([1, 2, 3]);
        $this->assertSame(1.0, $this->metric->getValue());

        $this->metric->setValue([]);
        $this->assertSame(1.0, $this->metric->getValue());
    }

    public function test_setValue_withObjectValue(): void
    {
        $obj = new \stdClass();
        $this->metric->setValue($obj);
        $this->assertSame(1.0, $this->metric->getValue());
    }

    public function test_setValue_returnsFluentInterface(): void
    {
        $result = $this->metric->setValue(100);
        $this->assertSame($this->metric, $result);
    }

    public function test_setAndGetCreateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-15 10:30:00');

        $this->metric->setCreateTime($dateTime);

        $this->assertSame($dateTime, $this->metric->getCreateTime());

        // 测试设置为null
        $this->metric->setCreateTime(null);
        $this->assertNull($this->metric->getCreateTime());
    }

    public function test_setAndGetUpdateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-15 15:45:00');

        $this->metric->setUpdateTime($dateTime);

        $this->assertSame($dateTime, $this->metric->getUpdateTime());

        // 测试设置为null
        $this->metric->setUpdateTime(null);
        $this->assertNull($this->metric->getUpdateTime());
    }

    public function test_setValue_withEdgeCases(): void
    {
        // 非常大的数字
        $this->metric->setValue(PHP_FLOAT_MAX);
        $this->assertSame(PHP_FLOAT_MAX, $this->metric->getValue());

        // 非常小的数字
        $this->metric->setValue(-PHP_FLOAT_MAX);
        $this->assertSame(-PHP_FLOAT_MAX, $this->metric->getValue());

        // NaN - 这会转换为0
        $this->metric->setValue(NAN);
        $this->assertTrue(is_nan($this->metric->getValue()) || $this->metric->getValue() === 0.0);

        // 无穷大
        $this->metric->setValue(INF);
        $this->assertTrue(is_infinite($this->metric->getValue()) || $this->metric->getValue() === 0.0);
    }

    public function test_fullMetricSetup(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        $createTime = new \DateTimeImmutable('2024-01-15 10:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-15 11:00:00');

        // setId 不支持链式调用
        $this->metric->setId(999);
        $this->metric->setMetricId('order_count')
            ->setMetricName('订单数量')
            ->setMetricUnit('个')
            ->setCategory('订单相关')
            ->setValue(150)
            ->setReport($report);

        $this->metric->setCreateTime($createTime);
        $this->metric->setUpdateTime($updateTime);

        $this->assertSame(999, $this->metric->getId());
        $this->assertSame('order_count', $this->metric->getMetricId());
        $this->assertSame('订单数量', $this->metric->getMetricName());
        $this->assertSame('个', $this->metric->getMetricUnit());
        $this->assertSame('订单相关', $this->metric->getCategory());
        $this->assertSame(150.0, $this->metric->getValue());
        $this->assertSame($report, $this->metric->getReport());
        $this->assertSame($createTime, $this->metric->getCreateTime());
        $this->assertSame($updateTime, $this->metric->getUpdateTime());
    }

    public function test_setValue_withMixedNumericString(): void
    {
        // 包含数字和字母的字符串
        $this->metric->setValue('123abc');
        $this->assertSame(123.0, $this->metric->getValue());

        $this->metric->setValue('abc123');
        $this->assertSame(0.0, $this->metric->getValue());

        $this->metric->setValue('12.34xyz');
        $this->assertSame(12.34, $this->metric->getValue());
    }
}
