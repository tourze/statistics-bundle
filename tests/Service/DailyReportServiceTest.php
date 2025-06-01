<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Metric\MetricProviderInterface;
use StatisticsBundle\Repository\DailyReportRepository;
use StatisticsBundle\Service\DailyReportService;

class DailyReportServiceTest extends TestCase
{
    private DailyReportService $service;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;
    /** @var DailyReportRepository&MockObject */
    private DailyReportRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(DailyReportRepository::class);
        
        $this->service = new DailyReportService(
            $this->entityManager,
            $this->repository,
            []
        );
    }

    public function test_registerMetricProvider(): void
    {
        $provider = new TestMetricProvider();
        
        $this->service->registerMetricProvider($provider);
        
        $providers = $this->service->getMetricProviders();
        $this->assertArrayHasKey('test_metric', $providers);
        $this->assertSame($provider, $providers['test_metric']);
    }

    public function test_getMetricProviders_initiallyEmpty(): void
    {
        $providers = $this->service->getMetricProviders();
        $this->assertIsArray($providers);
        $this->assertEmpty($providers);
    }

    public function test_getMetricProviders_afterRegistration(): void
    {
        $provider1 = new TestMetricProvider();
        $provider2 = $this->createMockProvider('provider2', 'Provider 2');
        
        $this->service->registerMetricProvider($provider1);
        $this->service->registerMetricProvider($provider2);
        
        $providers = $this->service->getMetricProviders();
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('test_metric', $providers);
        $this->assertArrayHasKey('provider2', $providers);
    }

    public function test_getMetricProvider_existing(): void
    {
        $provider = new TestMetricProvider();
        $this->service->registerMetricProvider($provider);
        
        $result = $this->service->getMetricProvider('test_metric');
        
        $this->assertSame($provider, $result);
    }

    public function test_getMetricProvider_nonExistent(): void
    {
        $result = $this->service->getMetricProvider('non_existent');
        
        $this->assertNull($result);
    }

    public function test_createOrUpdateDailyReport_newReport(): void
    {
        $reportDate = '2024-01-15';
        $metrics = ['metric1' => 100, 'metric2' => 200];
        $extraData = ['key' => 'value'];
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn(null);
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->service->createOrUpdateDailyReport($reportDate, $metrics, $extraData);
        
        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame($reportDate, $result->getReportDate());
        $this->assertSame($extraData, $result->getExtraData());
        $this->assertSame(100.0, $result->getMetricValue('metric1'));
        $this->assertSame(200.0, $result->getMetricValue('metric2'));
    }

    public function test_createOrUpdateDailyReport_existingReport(): void
    {
        $reportDate = '2024-01-15';
        $existingReport = new DailyReport();
        $existingReport->setReportDate($reportDate);
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn($existingReport);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($existingReport);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->service->createOrUpdateDailyReport($reportDate);
        
        $this->assertSame($existingReport, $result);
    }

    public function test_createOrUpdateDailyReport_withEmptyData(): void
    {
        $reportDate = '2024-01-15';
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->willReturn(null);
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->service->createOrUpdateDailyReport($reportDate);
        
        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame($reportDate, $result->getReportDate());
        $this->assertNull($result->getExtraData());
        $this->assertCount(0, $result->getMetrics());
    }

    public function test_getDailyReport(): void
    {
        $reportDate = '2024-01-15';
        $expectedReport = new DailyReport();
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn($expectedReport);
        
        $result = $this->service->getDailyReport($reportDate);
        
        $this->assertSame($expectedReport, $result);
    }

    public function test_getDailyReport_notFound(): void
    {
        $reportDate = '2024-01-15';
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn(null);
        
        $result = $this->service->getDailyReport($reportDate);
        
        $this->assertNull($result);
    }

    public function test_getDailyReportsByDateRange(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';
        $expectedReports = [new DailyReport(), new DailyReport()];
        
        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with($startDate, $endDate)
            ->willReturn($expectedReports);
        
        $result = $this->service->getDailyReportsByDateRange($startDate, $endDate);
        
        $this->assertSame($expectedReports, $result);
    }

    public function test_getRecentDailyReports_defaultDays(): void
    {
        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($startDate) {
                    return $startDate === date('Y-m-d', strtotime('-7 days'));
                }),
                date('Y-m-d')
            )
            ->willReturn([]);
        
        $result = $this->service->getRecentDailyReports();
        
        $this->assertIsArray($result);
    }

    public function test_getRecentDailyReports_customDays(): void
    {
        $days = 30;
        
        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($startDate) use ($days) {
                    return $startDate === date('Y-m-d', strtotime("-{$days} days"));
                }),
                date('Y-m-d')
            )
            ->willReturn([]);
        
        $result = $this->service->getRecentDailyReports($days);
        
        $this->assertIsArray($result);
    }

    public function test_deleteDailyReport_existing(): void
    {
        $reportDate = '2024-01-15';
        $report = new DailyReport();
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn($report);
        
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($report);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->service->deleteDailyReport($reportDate);
        
        $this->assertTrue($result);
    }

    public function test_deleteDailyReport_nonExistent(): void
    {
        $reportDate = '2024-01-15';
        
        $this->repository->expects($this->once())
            ->method('findByDate')
            ->with($reportDate)
            ->willReturn(null);
        
        $this->entityManager->expects($this->never())
            ->method('remove');
        
        $this->entityManager->expects($this->never())
            ->method('flush');
        
        $result = $this->service->deleteDailyReport($reportDate);
        
        $this->assertFalse($result);
    }

    public function test_serviceWithMetricProvidersInConstructor(): void
    {
        $provider1 = new TestMetricProvider();
        $provider2 = $this->createMockProvider('provider2', 'Provider 2');
        
        $service = new DailyReportService(
            $this->entityManager,
            $this->repository,
            [$provider1, $provider2]
        );
        
        $providers = $service->getMetricProviders();
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('test_metric', $providers);
        $this->assertArrayHasKey('provider2', $providers);
    }

    public function test_serviceWithNonMetricProviderInConstructor(): void
    {
        $invalidProvider = new \stdClass();
        
        $service = new DailyReportService(
            $this->entityManager,
            $this->repository,
            [$invalidProvider] // 无效的提供者
        );
        
        $providers = $service->getMetricProviders();
        $this->assertEmpty($providers);
    }

    /**
     * @return MetricProviderInterface&MockObject
     */
    private function createMockProvider(string $id, string $name): MetricProviderInterface
    {
        $provider = $this->createMock(MetricProviderInterface::class);
        $provider->method('getMetricId')->willReturn($id);
        $provider->method('getMetricName')->willReturn($name);
        $provider->method('getMetricDescription')->willReturn('Test description');
        $provider->method('getMetricUnit')->willReturn('count');
        $provider->method('getCategory')->willReturn('test');
        $provider->method('getCategoryOrder')->willReturn(1);
        
        return $provider;
    }
}

/**
 * 测试用的指标提供者实现
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

    public function getMetricValue(\Carbon\CarbonImmutable $date): mixed
    {
        return $date->dayOfYear;
    }
} 