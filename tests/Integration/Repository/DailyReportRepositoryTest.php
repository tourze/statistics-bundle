<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration\Repository;

use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Repository\DailyReportRepository;
use StatisticsBundle\Tests\Integration\DatabaseTestCase;

class DailyReportRepositoryTest extends DatabaseTestCase
{
    private DailyReportRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(DailyReportRepository::class);
    }

    public function testRepositoryIsRegistered(): void
    {
        $this->assertInstanceOf(DailyReportRepository::class, $this->repository);
    }

    public function testFindByDate(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $result = $this->repository->findByDate('2024-01-15');
        
        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame('2024-01-15', $result->getReportDate());
    }

    public function testFindByDateNotFound(): void
    {
        $result = $this->repository->findByDate('2024-01-15');
        
        $this->assertNull($result);
    }

    public function testFindByDateRange(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');
        
        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');
        
        $report3 = new DailyReport();
        $report3->setReportDate('2024-01-20');
        
        $this->entityManager->persist($report1);
        $this->entityManager->persist($report2);
        $this->entityManager->persist($report3);
        $this->entityManager->flush();

        $result = $this->repository->findByDateRange('2024-01-15', '2024-01-17');
        
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DailyReport::class, $result);
        $this->assertSame('2024-01-15', $result[0]->getReportDate());
        $this->assertSame('2024-01-16', $result[1]->getReportDate());
    }

    public function testFindByDateRangeEmpty(): void
    {
        $result = $this->repository->findByDateRange('2024-01-15', '2024-01-17');
        
        $this->assertEmpty($result);
    }

    public function testFindByDateRangeSingleDay(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');
        
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $result = $this->repository->findByDateRange('2024-01-15', '2024-01-15');
        
        $this->assertCount(1, $result);
        $this->assertSame('2024-01-15', $result[0]->getReportDate());
    }
}