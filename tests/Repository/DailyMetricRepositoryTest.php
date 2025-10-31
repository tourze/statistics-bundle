<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Repository\DailyMetricRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(DailyMetricRepository::class)]
#[RunTestsInSeparateProcesses]
final class DailyMetricRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Initialize test environment
        // DataFixtures will be loaded automatically by the testing framework
    }

    protected function createNewEntity(): object
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-' . rand(10, 28));
        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $entity = new DailyMetric();
        $entity->setMetricId('test_metric_' . uniqid());
        $entity->setMetricName('Test Metric ' . uniqid());
        $entity->setValue((float) rand(1, 1000));
        $entity->setReport($report);

        return $entity;
    }

    protected function getRepository(): DailyMetricRepository
    {
        return self::getService(DailyMetricRepository::class);
    }

    public function testRepositoryIsRegistered(): void
    {
        $this->assertInstanceOf(DailyMetricRepository::class, $this->getRepository());
    }

    public function testFindByReportId(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric1 = new DailyMetric();
        $metric1->setMetricId('test_metric_1');
        $metric1->setMetricName('Test Metric 1');
        $metric1->setValue(100.0);
        $metric1->setReport($report);

        $metric2 = new DailyMetric();
        $metric2->setMetricId('test_metric_2');
        $metric2->setMetricName('Test Metric 2');
        $metric2->setValue(200.0);
        $metric2->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric1);
        self::getEntityManager()->persist($metric2);
        self::getEntityManager()->flush();

        $reportId = $report->getId();
        $this->assertNotNull($reportId);
        $result = $this->getRepository()->findByReportId($reportId);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DailyMetric::class, $result);
    }

    public function testFindByReportAndMetricId(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');
        $metric->setValue(100.0);
        $metric->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric);
        self::getEntityManager()->flush();

        $reportId = $report->getId();
        $this->assertNotNull($reportId);
        $result = $this->getRepository()->findByReportAndMetricId($reportId, 'test_metric');

        $this->assertInstanceOf(DailyMetric::class, $result);
        $this->assertSame('test_metric', $result->getMetricId());
        $this->assertSame(100.0, $result->getValue());
    }

    public function testFindByReportAndMetricIdNotFound(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $reportId = $report->getId();
        $this->assertNotNull($reportId);
        $result = $this->getRepository()->findByReportAndMetricId($reportId, 'non_existent');

        $this->assertNull($result);
    }

    public function testGetMetricValuesForReportsEmpty(): void
    {
        $result = $this->getRepository()->getMetricValuesForReports([]);

        $this->assertEmpty($result);
    }

    public function testGetMetricValuesForReports(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');

        $metric1_1 = new DailyMetric();
        $metric1_1->setMetricId('metric_1');
        $metric1_1->setMetricName('Metric 1');
        $metric1_1->setValue(100.0);
        $metric1_1->setReport($report1);

        $metric1_2 = new DailyMetric();
        $metric1_2->setMetricId('metric_1');
        $metric1_2->setMetricName('Metric 1');
        $metric1_2->setValue(150.0);
        $metric1_2->setReport($report2);

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->persist($metric1_1);
        self::getEntityManager()->persist($metric1_2);
        self::getEntityManager()->flush();

        $reportId1 = $report1->getId();
        $reportId2 = $report2->getId();
        $this->assertNotNull($reportId1);
        $this->assertNotNull($reportId2);
        $result = $this->getRepository()->getMetricValuesForReports([$reportId1, $reportId2]);

        $this->assertArrayHasKey('metric_1', $result);
        $this->assertArrayHasKey($reportId1, $result['metric_1']);
        $this->assertArrayHasKey($reportId2, $result['metric_1']);
        $this->assertSame(100.0, $result['metric_1'][$reportId1]);
        $this->assertSame(150.0, $result['metric_1'][$reportId2]);
    }

    public function testSave(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');
        $metric->setValue(100.0);
        $metric->setReport($report);

        $this->getRepository()->save($metric);

        $savedMetric = $this->getRepository()->find($metric->getId());
        $this->assertInstanceOf(DailyMetric::class, $savedMetric);
        $this->assertSame('test_metric', $savedMetric->getMetricId());
    }

    public function testSaveWithoutFlush(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');
        $metric->setValue(100.0);
        $metric->setReport($report);

        $this->getRepository()->save($metric, false);
        self::getEntityManager()->flush();

        $savedMetric = $this->getRepository()->find($metric->getId());
        $this->assertInstanceOf(DailyMetric::class, $savedMetric);
        $this->assertSame('test_metric', $savedMetric->getMetricId());
    }

    public function testRemove(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric = new DailyMetric();
        $metric->setMetricId('test_metric');
        $metric->setMetricName('Test Metric');
        $metric->setValue(100.0);
        $metric->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric);
        self::getEntityManager()->flush();

        $metricId = $metric->getId();
        $this->getRepository()->remove($metric);

        $removedMetric = $this->getRepository()->find($metricId);
        $this->assertNull($removedMetric);
    }

    public function testFindByWithNullableFields(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric1 = new DailyMetric();
        $metric1->setMetricId('test_metric_with_unit');
        $metric1->setMetricName('Test Metric With Unit');
        $metric1->setValue(100.0);
        $metric1->setMetricUnit('kg');
        $metric1->setCategory('category_a');
        $metric1->setReport($report);

        $metric2 = new DailyMetric();
        $metric2->setMetricId('test_metric_without_unit');
        $metric2->setMetricName('Test Metric Without Unit');
        $metric2->setValue(200.0);
        $metric2->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric1);
        self::getEntityManager()->persist($metric2);
        self::getEntityManager()->flush();

        $nullUnitMetrics = $this->getRepository()->findBy(['metricId' => 'test_metric_without_unit', 'metricUnit' => null]);
        $this->assertCount(1, $nullUnitMetrics);
        $this->assertSame('test_metric_without_unit', $nullUnitMetrics[0]->getMetricId());

        $nullCategoryMetrics = $this->getRepository()->findBy(['metricId' => 'test_metric_without_unit', 'category' => null]);
        $this->assertCount(1, $nullCategoryMetrics);
        $this->assertSame('test_metric_without_unit', $nullCategoryMetrics[0]->getMetricId());
    }

    public function testCountWithNullableFields(): void
    {
        // 获取初始计数
        $initialNullUnitCount = $this->getRepository()->count(['metricUnit' => null]);
        $initialNullCategoryCount = $this->getRepository()->count(['category' => null]);

        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric1 = new DailyMetric();
        $metric1->setMetricId('test_metric_with_unit');
        $metric1->setMetricName('Test Metric With Unit');
        $metric1->setValue(100.0);
        $metric1->setMetricUnit('kg');
        $metric1->setReport($report);

        $metric2 = new DailyMetric();
        $metric2->setMetricId('test_metric_without_unit');
        $metric2->setMetricName('Test Metric Without Unit');
        $metric2->setValue(200.0);
        $metric2->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric1);
        self::getEntityManager()->persist($metric2);
        self::getEntityManager()->flush();

        $nullUnitCount = $this->getRepository()->count(['metricUnit' => null]);
        $this->assertSame($initialNullUnitCount + 1, $nullUnitCount);

        $nullCategoryCount = $this->getRepository()->count(['category' => null]);
        $this->assertSame($initialNullCategoryCount + 2, $nullCategoryCount);
    }

    public function testFindByWithAssociationField(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric = new DailyMetric();
        $metric->setMetricId('test_metric_association');
        $metric->setMetricName('Test Metric Association');
        $metric->setValue(100.0);
        $metric->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric);
        self::getEntityManager()->flush();

        $metricsForReport = $this->getRepository()->findBy(['report' => $report, 'metricId' => 'test_metric_association']);
        $this->assertCount(1, $metricsForReport);
        $this->assertSame('test_metric_association', $metricsForReport[0]->getMetricId());
    }

    public function testCountWithAssociationField(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        // 获取该报告的初始计数
        $initialCount = $this->getRepository()->count(['report' => $report]);

        $metric1 = new DailyMetric();
        $metric1->setMetricId('test_metric_1');
        $metric1->setMetricName('Test Metric 1');
        $metric1->setValue(100.0);
        $metric1->setReport($report);

        $metric2 = new DailyMetric();
        $metric2->setMetricId('test_metric_2');
        $metric2->setMetricName('Test Metric 2');
        $metric2->setValue(200.0);
        $metric2->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric1);
        self::getEntityManager()->persist($metric2);
        self::getEntityManager()->flush();

        $countForReport = $this->getRepository()->count(['report' => $report]);
        $this->assertSame($initialCount + 2, $countForReport);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $metric1 = new DailyMetric();
        $metric1->setMetricId('test_metric_z');
        $metric1->setMetricName('Test Metric Z');
        $metric1->setValue(100.0);
        $metric1->setReport($report);

        $metric2 = new DailyMetric();
        $metric2->setMetricId('test_metric_a');
        $metric2->setMetricName('Test Metric A');
        $metric2->setValue(200.0);
        $metric2->setReport($report);

        self::getEntityManager()->persist($report);
        self::getEntityManager()->persist($metric1);
        self::getEntityManager()->persist($metric2);
        self::getEntityManager()->flush();

        $firstByIdAsc = $this->getRepository()->findOneBy(['report' => $report], ['metricId' => 'ASC']);
        $this->assertInstanceOf(DailyMetric::class, $firstByIdAsc);
        $this->assertSame('test_metric_a', $firstByIdAsc->getMetricId());

        $firstByIdDesc = $this->getRepository()->findOneBy(['report' => $report], ['metricId' => 'DESC']);
        $this->assertInstanceOf(DailyMetric::class, $firstByIdDesc);
        $this->assertSame('test_metric_z', $firstByIdDesc->getMetricId());
    }
}
