<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(DailyMetric::class)]
final class DailyMetricTest extends AbstractEntityTestCase
{
    private DailyMetric $metric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metric = new DailyMetric();
    }

    protected function createEntity(): object
    {
        return new DailyMetric();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'metricId' => ['metricId', 'test_metric_id'];
        yield 'metricName' => ['metricName', 'Test Metric Name'];
        yield 'metricUnit' => ['metricUnit', 'count'];
        yield 'category' => ['category', 'Test Category'];
        yield 'value' => ['value', 100.5];
    }

    public function testInitialValues(): void
    {
        $this->assertSame(0, $this->metric->getId());
        $this->assertSame(0.0, $this->metric->getValue());
        $this->assertNull($this->metric->getCreateTime());
        $this->assertNull($this->metric->getUpdateTime());
        $this->assertNull($this->metric->getMetricUnit());
        $this->assertNull($this->metric->getCategory());
    }

    public function testSetAndGetId(): void
    {
        $this->metric->setId(123);
        $this->assertSame(123, $this->metric->getId());

        $this->metric->setId(null);
        $this->assertNull($this->metric->getId());
    }

    public function testSetAndGetMetricId(): void
    {
        $metricId = 'user_registration_count';

        $this->metric->setMetricId($metricId);

        $this->assertSame($metricId, $this->metric->getMetricId());
    }

    public function testSetAndGetMetricName(): void
    {
        $metricName = '用户注册数量';

        $this->metric->setMetricName($metricName);

        $this->assertSame($metricName, $this->metric->getMetricName());
    }

    public function testSetAndGetMetricUnit(): void
    {
        $unit = '人';

        $this->metric->setMetricUnit($unit);

        $this->assertSame($unit, $this->metric->getMetricUnit());

        // 测试设置为null
        $this->metric->setMetricUnit(null);
        $this->assertNull($this->metric->getMetricUnit());
    }

    public function testSetAndGetCategory(): void
    {
        $category = '用户相关';

        $this->metric->setCategory($category);

        $this->assertSame($category, $this->metric->getCategory());

        // 测试设置为null
        $this->metric->setCategory(null);
        $this->assertNull($this->metric->getCategory());
    }

    public function testSetAndGetReport(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $this->metric->setReport($report);

        $this->assertSame($report, $this->metric->getReport());
    }

    public function testSetValueWithNumericValues(): void
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

    public function testSetValueWithBooleanValues(): void
    {
        $this->metric->setValue(true);
        $this->assertSame(1.0, $this->metric->getValue());

        $this->metric->setValue(false);
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function testSetValueWithStringValues(): void
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

    public function testSetValueWithNullValue(): void
    {
        $this->metric->setValue(null);
        $this->assertSame(0.0, $this->metric->getValue());
    }

    public function testSetValueWithArrayValue(): void
    {
        $this->metric->setValue([1, 2, 3]);
        $this->assertSame(1.0, $this->metric->getValue());

        $this->metric->setValue([]);
        $this->assertSame(1.0, $this->metric->getValue());
    }

    public function testSetValueWithObjectValue(): void
    {
        $obj = new \stdClass();
        $this->metric->setValue($obj);
        $this->assertSame(1.0, $this->metric->getValue());
    }

    public function testSetValueReturnsVoid(): void
    {
        $this->metric->setValue(100);
        // setValue returns void, so we just check the value was set correctly
        $this->assertSame(100.0, $this->metric->getValue());
    }

    public function testSetAndGetCreateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-15 10:30:00');

        $this->metric->setCreateTime($dateTime);

        $this->assertSame($dateTime, $this->metric->getCreateTime());

        // 测试设置为null
        $this->metric->setCreateTime(null);
        $this->assertNull($this->metric->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2024-01-15 15:45:00');

        $this->metric->setUpdateTime($dateTime);

        $this->assertSame($dateTime, $this->metric->getUpdateTime());

        // 测试设置为null
        $this->metric->setUpdateTime(null);
        $this->assertNull($this->metric->getUpdateTime());
    }

    public function testSetValueWithEdgeCases(): void
    {
        // 非常大的数字
        $this->metric->setValue(PHP_FLOAT_MAX);
        $this->assertSame(PHP_FLOAT_MAX, $this->metric->getValue());

        // 非常小的数字
        $this->metric->setValue(-PHP_FLOAT_MAX);
        $this->assertSame(-PHP_FLOAT_MAX, $this->metric->getValue());

        // NaN - 这会转换为0
        $this->metric->setValue(NAN);
        $this->assertTrue(is_nan($this->metric->getValue()) || 0.0 === $this->metric->getValue());

        // 无穷大
        $this->metric->setValue(INF);
        $this->assertTrue(is_infinite($this->metric->getValue()) || 0.0 === $this->metric->getValue());
    }

    public function testFullMetricSetup(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        $createTime = new \DateTimeImmutable('2024-01-15 10:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-15 11:00:00');

        // setter方法现在都返回void
        $this->metric->setId(999);
        $this->metric->setMetricId('order_count');
        $this->metric->setMetricName('订单数量');
        $this->metric->setMetricUnit('个');
        $this->metric->setCategory('订单相关');
        $this->metric->setValue(150);
        $this->metric->setReport($report);

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

    public function testSetValueWithMixedNumericString(): void
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
