<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use Tourze\Arrayable\PlainArrayInterface;

class DailyReportTest extends TestCase
{
    private DailyReport $report;

    protected function setUp(): void
    {
        $this->report = new DailyReport();
    }

    public function test_implementsPlainArrayInterface(): void
    {
        $this->assertInstanceOf(PlainArrayInterface::class, $this->report);
    }

    public function test_initialValues(): void
    {
        $this->assertSame(0, $this->report->getId());
        $this->assertInstanceOf(ArrayCollection::class, $this->report->getMetrics());
        $this->assertCount(0, $this->report->getMetrics());
        $this->assertNull($this->report->getExtraData());
        $this->assertNull($this->report->getCreateTime());
        $this->assertNull($this->report->getUpdateTime());
    }

    public function test_setAndGetId(): void
    {
        $this->report->setId(123);
        $this->assertSame(123, $this->report->getId());
        
        $this->report->setId(null);
        $this->assertNull($this->report->getId());
    }

    public function test_setAndGetReportDate(): void
    {
        $date = '2024-01-15';
        
        $result = $this->report->setReportDate($date);
        
        $this->assertSame($date, $this->report->getReportDate());
        $this->assertSame($this->report, $result);
    }

    public function test_setReportDate_withDifferentFormats(): void
    {
        $dates = [
            '2024-01-01',
            '2023-12-31',
            '2024-02-29', // 闰年
            '2024-06-15',
        ];

        foreach ($dates as $date) {
            $this->report->setReportDate($date);
            $this->assertSame($date, $this->report->getReportDate());
        }
    }

    public function test_addMetric(): void
    {
        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');

        $result = $this->report->addMetric($metric);

        $this->assertSame($this->report, $result);
        $this->assertCount(1, $this->report->getMetrics());
        $this->assertTrue($this->report->getMetrics()->contains($metric));
        $this->assertSame($this->report, $metric->getReport());
    }

    public function test_addMetric_duplicateMetric(): void
    {
        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');

        $this->report->addMetric($metric);
        $this->report->addMetric($metric); // 重复添加

        $this->assertCount(1, $this->report->getMetrics());
    }

    public function test_removeMetric(): void
    {
        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');

        $this->report->addMetric($metric);
        $this->assertCount(1, $this->report->getMetrics());

        $result = $this->report->removeMetric($metric);

        $this->assertSame($this->report, $result);
        $this->assertCount(0, $this->report->getMetrics());
        $this->assertFalse($this->report->getMetrics()->contains($metric));
    }

    public function test_removeMetric_nonExistentMetric(): void
    {
        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');

        $result = $this->report->removeMetric($metric);

        $this->assertSame($this->report, $result);
        $this->assertCount(0, $this->report->getMetrics());
    }

    public function test_getMetricValue(): void
    {
        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');
        $metric->setValue(100);

        $this->report->addMetric($metric);

        $this->assertSame(100.0, $this->report->getMetricValue('test_metric'));
    }

    public function test_getMetricValue_withDefault(): void
    {
        $value = $this->report->getMetricValue('non_existent_metric', 'default_value');
        $this->assertSame('default_value', $value);
    }

    public function test_getMetricValue_withNullDefault(): void
    {
        $value = $this->report->getMetricValue('non_existent_metric');
        $this->assertNull($value);
    }

    public function test_setMetricValue_newMetric(): void
    {
        $result = $this->report->setMetricValue('test_metric', 'Test Metric', 150);

        $this->assertSame($this->report, $result);
        $this->assertCount(1, $this->report->getMetrics());
        $this->assertSame(150.0, $this->report->getMetricValue('test_metric'));
        
        $metric = $this->report->findMetric('test_metric');
        $this->assertNotNull($metric);
        $this->assertSame('Test Metric', $metric->getMetricName());
    }

    public function test_setMetricValue_existingMetric(): void
    {
        // 先添加一个指标
        $this->report->setMetricValue('test_metric', 'Test Metric', 100);
        
        // 更新同一个指标
        $this->report->setMetricValue('test_metric', 'Updated Metric', 200, 'units', 'category');

        $this->assertCount(1, $this->report->getMetrics());
        $this->assertSame(200.0, $this->report->getMetricValue('test_metric'));
        
        $metric = $this->report->findMetric('test_metric');
        $this->assertNotNull($metric);
        $this->assertSame('Updated Metric', $metric->getMetricName());
        $this->assertSame('units', $metric->getMetricUnit());
        $this->assertSame('category', $metric->getCategory());
    }

    public function test_setMetricValue_withUnitAndCategory(): void
    {
        $this->report->setMetricValue('test_metric', 'Test Metric', 75, '人', '用户统计');

        $metric = $this->report->findMetric('test_metric');
        $this->assertNotNull($metric);
        $this->assertSame('人', $metric->getMetricUnit());
        $this->assertSame('用户统计', $metric->getCategory());
        $this->assertSame(75.0, $metric->getValue());
    }

    public function test_hasMetric(): void
    {
        $this->assertFalse($this->report->hasMetric('non_existent'));

        $this->report->setMetricValue('test_metric', 'Test Metric', 100);
        
        $this->assertTrue($this->report->hasMetric('test_metric'));
        $this->assertFalse($this->report->hasMetric('another_metric'));
    }

    public function test_findMetric(): void
    {
        $this->assertNull($this->report->findMetric('non_existent'));

        $this->report->setMetricValue('test_metric', 'Test Metric', 100);
        
        $metric = $this->report->findMetric('test_metric');
        $this->assertInstanceOf(DailyMetric::class, $metric);
        $this->assertSame('test_metric', $metric->getMetricId());
    }

    public function test_addMetrics_withArrayFormat(): void
    {
        $metrics = [
            'metric1' => [
                'name' => 'Metric 1',
                'value' => 100,
                'unit' => 'count',
                'category' => 'category1'
            ],
            'metric2' => [
                'name' => 'Metric 2',
                'value' => 200,
                'unit' => 'amount',
                'category' => 'category2'
            ],
        ];

        $result = $this->report->addMetrics($metrics);

        $this->assertSame($this->report, $result);
        $this->assertCount(2, $this->report->getMetrics());
        $this->assertSame(100.0, $this->report->getMetricValue('metric1'));
        $this->assertSame(200.0, $this->report->getMetricValue('metric2'));
        
        $metric1 = $this->report->findMetric('metric1');
        $this->assertSame('Metric 1', $metric1->getMetricName());
        $this->assertSame('count', $metric1->getMetricUnit());
        $this->assertSame('category1', $metric1->getCategory());
    }

    public function test_addMetrics_withLegacyFormat(): void
    {
        $metrics = [
            'metric1' => 100,
            'metric2' => 200,
        ];

        $this->report->addMetrics($metrics);

        $this->assertCount(2, $this->report->getMetrics());
        $this->assertSame(100.0, $this->report->getMetricValue('metric1'));
        $this->assertSame(200.0, $this->report->getMetricValue('metric2'));
        
        $metric1 = $this->report->findMetric('metric1');
        $this->assertSame('metric1', $metric1->getMetricName());
    }

    public function test_addMetrics_withMixedFormats(): void
    {
        $metrics = [
            'metric1' => [
                'name' => 'Metric 1',
                'value' => 100,
            ],
            'metric2' => 200, // 旧格式
        ];

        $this->report->addMetrics($metrics);

        $this->assertCount(2, $this->report->getMetrics());
        $this->assertSame(100.0, $this->report->getMetricValue('metric1'));
        $this->assertSame(200.0, $this->report->getMetricValue('metric2'));
    }

    public function test_setAndGetExtraData(): void
    {
        $extraData = ['key1' => 'value1', 'key2' => 123];
        
        $result = $this->report->setExtraData($extraData);
        
        $this->assertSame($this->report, $result);
        $this->assertSame($extraData, $this->report->getExtraData());
        
        // 测试设置为null
        $this->report->setExtraData(null);
        $this->assertNull($this->report->getExtraData());
    }

    public function test_setAndGetCreateTime(): void
    {
        $dateTime = new \DateTime('2024-01-15 10:30:00');
        
        $this->report->setCreateTime($dateTime);
        
        $this->assertSame($dateTime, $this->report->getCreateTime());
        
        // 测试设置为null
        $this->report->setCreateTime(null);
        $this->assertNull($this->report->getCreateTime());
    }

    public function test_setAndGetUpdateTime(): void
    {
        $dateTime = new \DateTime('2024-01-15 15:45:00');
        
        $this->report->setUpdateTime($dateTime);
        
        $this->assertSame($dateTime, $this->report->getUpdateTime());
        
        // 测试设置为null
        $this->report->setUpdateTime(null);
        $this->assertNull($this->report->getUpdateTime());
    }

    public function test_retrievePlainArray(): void
    {
        $this->report->setId(123);
        $this->report->setReportDate('2024-01-15');
        $this->report->setExtraData(['test_key' => 'test_value']);
        
        $createTime = new \DateTime('2024-01-15 10:00:00');
        $updateTime = new \DateTime('2024-01-15 11:00:00');
        $this->report->setCreateTime($createTime);
        $this->report->setUpdateTime($updateTime);
        
        $this->report->setMetricValue('metric1', 'Metric 1', 100, 'count', 'category1');
        $this->report->setMetricValue('metric2', 'Metric 2', 200, 'amount', 'category2');
        
        $result = $this->report->retrievePlainArray();
        
        $expected = [
            'id' => 123,
            'reportDate' => '2024-01-15',
            'metrics' => [
                'metric1' => [
                    'name' => 'Metric 1',
                    'value' => 100.0,
                    'unit' => 'count',
                    'category' => 'category1'
                ],
                'metric2' => [
                    'name' => 'Metric 2',
                    'value' => 200.0,
                    'unit' => 'amount',
                    'category' => 'category2'
                ]
            ],
            'extraData' => ['test_key' => 'test_value'],
            'createTime' => '2024-01-15 10:00:00',
            'updateTime' => '2024-01-15 11:00:00',
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function test_retrievePlainArray_withNullTimes(): void
    {
        $this->report->setId(456);
        $this->report->setReportDate('2024-01-16');
        
        $result = $this->report->retrievePlainArray();
        
        $this->assertSame(456, $result['id']);
        $this->assertSame('2024-01-16', $result['reportDate']);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
        $this->assertNull($result['extraData']);
        $this->assertSame([], $result['metrics']);
    }

    public function test_retrievePlainArray_withEmptyMetrics(): void
    {
        $this->report->setReportDate('2024-01-17');
        
        $result = $this->report->retrievePlainArray();
        
        $this->assertSame([], $result['metrics']);
    }
} 