<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration\Repository;

use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Repository\DailyMetricRepository;
use StatisticsBundle\Tests\Integration\DatabaseTestCase;

class DailyMetricRepositoryTest extends DatabaseTestCase
{
    private DailyMetricRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(DailyMetricRepository::class);
    }

    public function testRepositoryIsRegistered(): void
    {
        $this->assertInstanceOf(DailyMetricRepository::class, $this->repository);
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

        $this->entityManager->persist($report);
        $this->entityManager->persist($metric1);
        $this->entityManager->persist($metric2);
        $this->entityManager->flush();

        $result = $this->repository->findByReportId($report->getId());
        
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

        $this->entityManager->persist($report);
        $this->entityManager->persist($metric);
        $this->entityManager->flush();

        $result = $this->repository->findByReportAndMetricId($report->getId(), 'test_metric');
        
        $this->assertInstanceOf(DailyMetric::class, $result);
        $this->assertSame('test_metric', $result->getMetricId());
        $this->assertSame(100.0, $result->getValue());
    }

    public function testFindByReportAndMetricIdNotFound(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $result = $this->repository->findByReportAndMetricId($report->getId(), 'non_existent');
        
        $this->assertNull($result);
    }

    public function testGetMetricValuesForReportsEmpty(): void
    {
        $result = $this->repository->getMetricValuesForReports([]);
        
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

        $this->entityManager->persist($report1);
        $this->entityManager->persist($report2);
        $this->entityManager->persist($metric1_1);
        $this->entityManager->persist($metric1_2);
        $this->entityManager->flush();

        $result = $this->repository->getMetricValuesForReports([$report1->getId(), $report2->getId()]);
        
        $this->assertArrayHasKey('metric_1', $result);
        $this->assertArrayHasKey($report1->getId(), $result['metric_1']);
        $this->assertArrayHasKey($report2->getId(), $result['metric_1']);
        $this->assertSame(100.0, $result['metric_1'][$report1->getId()]);
        $this->assertSame(150.0, $result['metric_1'][$report2->getId()]);
    }
}