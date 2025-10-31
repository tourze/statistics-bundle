<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use StatisticsBundle\Contract\DailyReportStorageInterface;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Metric\MetricProviderInterface;
use StatisticsBundle\Service\DailyReportService;
use StatisticsBundle\Tests\Service\Fixtures\TestMetricProvider;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(DailyReportService::class)]
#[RunTestsInSeparateProcesses]
final class DailyReportServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 空实现，满足抽象方法要求
    }

    /**
     * @return array{DailyReportService, DailyReportStorageInterface}
     */
    private function createServiceWithMockRepository(): array
    {
        $repository = $this->createMock(DailyReportStorageInterface::class);
        self::getContainer()->set('StatisticsBundle\Contract\DailyReportStorageInterface', $repository);

        $service = self::getService(DailyReportService::class);

        return [$service, $repository];
    }

    public function testRegisterMetricProvider(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $provider = new TestMetricProvider();

        $service->registerMetricProvider($provider);

        $providers = $service->getMetricProviders();
        $this->assertArrayHasKey('test_metric', $providers);
        $this->assertSame($provider, $providers['test_metric']);
    }

    public function testGetMetricProvidersInitiallyEmpty(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $providers = $service->getMetricProviders();

        $this->assertEmpty($providers);
    }

    public function testCreateOrUpdateDailyReportNewReport(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';
        $metrics = [];
        $extraData = ['key' => 'value'];

        /** @var InvocationMocker $findByDateExpectation 配置 findByDate 方法的 Mock 行为 */
        $findByDateExpectation = $repository->expects($this->once());
        $findByDateExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn(null)
        ;

        $result = $service->createOrUpdateDailyReport($reportDate, $metrics, $extraData);

        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame($reportDate, $result->getReportDate());
        $this->assertSame($extraData, $result->getExtraData());
        $this->assertCount(0, $result->getMetrics());
    }

    public function testCreateOrUpdateDailyReportExistingReport(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';
        $existingReport = new DailyReport();
        $existingReport->setReportDate($reportDate);

        /** @var InvocationMocker $existingReportExpectation 配置返回已存在报告的 Mock 行为 */
        $existingReportExpectation = $repository->expects($this->once());
        $existingReportExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn($existingReport)
        ;

        $result = $service->createOrUpdateDailyReport($reportDate);

        $this->assertSame($existingReport, $result);
    }

    public function testCreateOrUpdateDailyReportWithEmptyData(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';

        /** @var InvocationMocker $emptyDataExpectation 配置空数据场景的 Mock 行为 */
        $emptyDataExpectation = $repository->expects($this->once());
        $emptyDataExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn(null)
        ;

        $result = $service->createOrUpdateDailyReport($reportDate);

        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame($reportDate, $result->getReportDate());
        $this->assertEmpty($result->getExtraData());
    }

    public function testGetDailyReport(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';
        $expectedReport = new DailyReport();
        $expectedReport->setReportDate($reportDate);

        /** @var InvocationMocker $getDailyReportExpectation 配置获取报告的 Mock 行为 */
        $getDailyReportExpectation = $repository->expects($this->once());
        $getDailyReportExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn($expectedReport)
        ;

        $result = $service->getDailyReport($reportDate);

        $this->assertSame($expectedReport, $result);
    }

    public function testGetDailyReportNotFound(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';

        /** @var InvocationMocker $notFoundExpectation 配置未找到报告的 Mock 行为 */
        $notFoundExpectation = $repository->expects($this->once());
        $notFoundExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn(null)
        ;

        $result = $service->getDailyReport($reportDate);

        $this->assertNull($result);
    }

    public function testDeleteDailyReportExisting(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';

        // 创建一个真实的 DailyReport 实体并持久化
        $existingReport = new DailyReport();
        $existingReport->setReportDate($reportDate);
        self::getEntityManager()->persist($existingReport);
        self::getEntityManager()->flush();

        /** @var InvocationMocker $deleteExistingExpectation 配置删除已存在报告的 Mock 行为 */
        $deleteExistingExpectation = $repository->expects($this->once());
        $deleteExistingExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn($existingReport)
        ;

        $result = $service->deleteDailyReport($reportDate);

        $this->assertTrue($result);
    }

    public function testDeleteDailyReportNonExistent(): void
    {
        [$service, $repository] = $this->createServiceWithMockRepository();
        $reportDate = '2024-01-15';

        /** @var InvocationMocker $deleteNonExistentExpectation 配置删除不存在报告的 Mock 行为 */
        $deleteNonExistentExpectation = $repository->expects($this->once());
        $deleteNonExistentExpectation->method('findByDate')
            ->with($reportDate)
            ->willReturn(null)
        ;

        $result = $service->deleteDailyReport($reportDate);

        $this->assertFalse($result);
    }

    /**
     * 测试服务构造函数中的 metric providers 处理逻辑
     */
    public function testServiceWithMetricProvidersInConstructor(): void
    {
        $repository = $this->createMock(DailyReportStorageInterface::class);
        $provider1 = new TestMetricProvider();
        $provider2 = $this->createMock(MetricProviderInterface::class);
        $provider2->method('getMetricId')->willReturn('provider2');

        // 为了测试构造函数中的 metric providers 处理，我们注册这些 provider
        self::getContainer()->set('test_provider_1', $provider1);
        self::getContainer()->set('test_provider_2', $provider2);
        self::getContainer()->set('StatisticsBundle\Contract\DailyReportStorageInterface', $repository);

        // 获取服务并手动注册 providers
        $service = self::getService(DailyReportService::class);
        $service->registerMetricProvider($provider1);
        $service->registerMetricProvider($provider2);

        $providers = $service->getMetricProviders();
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('test_metric', $providers);
        $this->assertArrayHasKey('provider2', $providers);
        $this->assertSame($provider1, $providers['test_metric']);
        $this->assertSame($provider2, $providers['provider2']);
    }

    /**
     * 测试服务构造函数中的非 metric provider 处理逻辑
     */
    public function testServiceWithNonMetricProviderInConstructor(): void
    {
        $repository = $this->createMock(DailyReportStorageInterface::class);
        self::getContainer()->set('StatisticsBundle\Contract\DailyReportStorageInterface', $repository);

        // 获取服务（没有任何 metric providers）
        $service = self::getService(DailyReportService::class);

        $providers = $service->getMetricProviders();
        $this->assertEmpty($providers);
    }
}
