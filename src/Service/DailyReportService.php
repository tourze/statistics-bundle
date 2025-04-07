<?php

namespace StatisticsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Metric\MetricProviderInterface;
use StatisticsBundle\Repository\DailyReportRepository;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class DailyReportService
{
    /**
     * @var MetricProviderInterface[]
     */
    private array $metricProviders = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DailyReportRepository $dailyReportRepository,
        #[TaggedIterator(MetricProviderInterface::SERVICE_TAG)] iterable $metricProviders,
    ) {
        foreach ($metricProviders as $provider) {
            if ($provider instanceof MetricProviderInterface) {
                $this->registerMetricProvider($provider);
            }
        }
    }

    /**
     * 注册指标提供者
     */
    public function registerMetricProvider(MetricProviderInterface $provider): void
    {
        $this->metricProviders[$provider->getMetricId()] = $provider;
    }

    /**
     * 获取所有已注册的指标提供者
     *
     * @return MetricProviderInterface[]
     */
    public function getMetricProviders(): array
    {
        return $this->metricProviders;
    }

    /**
     * 通过ID获取指标提供者
     */
    public function getMetricProvider(string $metricId): ?MetricProviderInterface
    {
        return $this->metricProviders[$metricId] ?? null;
    }

    /**
     * 创建或更新日报数据
     */
    public function createOrUpdateDailyReport(string $reportDate, array $metrics = [], ?array $extraData = null): DailyReport
    {
        $report = $this->dailyReportRepository->findByDate($reportDate);

        if (!$report) {
            $report = new DailyReport();
            $report->setReportDate($reportDate);
        }

        if (!empty($metrics)) {
            $report->addMetrics($metrics);
        }

        if ($extraData !== null) {
            $report->setExtraData($extraData);
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 获取指定日期的报表
     */
    public function getDailyReport(string $reportDate): ?DailyReport
    {
        return $this->dailyReportRepository->findByDate($reportDate);
    }

    /**
     * 获取日期范围内的所有报表
     */
    public function getDailyReportsByDateRange(string $startDate, string $endDate): array
    {
        return $this->dailyReportRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * 获取最近几天的报表
     */
    public function getRecentDailyReports(int $days = 7): array
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return $this->getDailyReportsByDateRange($startDate, $endDate);
    }

    /**
     * 删除指定日期的报表
     */
    public function deleteDailyReport(string $reportDate): bool
    {
        $report = $this->dailyReportRepository->findByDate($reportDate);

        if (!$report) {
            return false;
        }

        $this->entityManager->remove($report);
        $this->entityManager->flush();

        return true;
    }
}
