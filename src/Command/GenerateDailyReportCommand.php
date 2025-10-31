<?php

namespace StatisticsBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Metric\MetricProviderInterface;
use StatisticsBundle\Service\DailyReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: self::NAME,
    description: '生成每日统计报告',
)]
class GenerateDailyReportCommand extends Command
{
    public const NAME = 'app:statistics:generate-daily-report';

    public function __construct(
        private readonly DailyReportService $dailyReportService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, '日期 (格式: YYYY-MM-DD)', date('Y-m-d', strtotime('-1 day')))
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制重新生成报告')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateOption = $input->getOption('date');
        if (!is_string($dateOption)) {
            throw new \InvalidArgumentException('Date option must be a string');
        }
        $date = CarbonImmutable::parse($dateOption);
        $force = $input->getOption('force');

        $this->showHeader($io, $date, (bool) $force);
        $report = $this->prepareReport($io, $date);
        $metrics = $this->generateMetrics($report, $date);
        $this->showResults($io, $metrics);

        return Command::SUCCESS;
    }

    private function showHeader(SymfonyStyle $io, CarbonImmutable $date, bool $force): void
    {
        $io->title('生成统计日报');
        $io->section("日期: {$date->toDateString()}" . ($force ? ' (强制更新)' : ''));

        $providers = $this->dailyReportService->getMetricProviders();
        $io->note(sprintf('已找到 %d 个指标提供者', count($providers)));
    }

    private function prepareReport(SymfonyStyle $io, CarbonImmutable $date): DailyReport
    {
        $report = $this->dailyReportService->getDailyReport($date->toDateString());
        if (null === $report) {
            $report = new DailyReport();
            $report->setReportDate($date->toDateString());
        }

        return $report;
    }

    /**
     * @return DailyMetric[]
     */
    private function generateMetrics(DailyReport $report, CarbonImmutable $date): array
    {
        $metrics = [];
        foreach ($this->dailyReportService->getMetricProviders() as $provider) {
            $metric = $this->createOrUpdateMetric($report, $provider, $date);
            $report->addMetric($metric);
            $this->entityManager->persist($metric);
            $this->entityManager->flush();
            $metrics[] = $metric;
        }

        return $metrics;
    }

    private function createOrUpdateMetric(DailyReport $report, MetricProviderInterface $provider, CarbonImmutable $date): DailyMetric
    {
        $metricId = $provider->getMetricId();
        $metric = $report->findMetric($metricId);

        if (null === $metric) {
            $metric = new DailyMetric();
            $metric->setMetricId($metricId);
        }

        $metric->setReport($report);
        $metric->setMetricName($provider->getMetricName());
        $metric->setMetricUnit($provider->getMetricUnit());
        $metric->setCategory($provider->getCategory());
        $metric->setValue($provider->getMetricValue($date));

        return $metric;
    }

    /**
     * @param DailyMetric[] $metrics
     */
    private function showResults(SymfonyStyle $io, array $metrics): void
    {
        $metricsCount = count($metrics);

        if ($metricsCount > 0) {
            $io->success(sprintf('成功生成报告，包含 %d 个指标', $metricsCount));
            $this->displayMetricsTable($io, $metrics);
        } else {
            $io->warning('未生成任何指标数据');
        }
    }

    /**
     * @param DailyMetric[] $metrics
     */
    private function displayMetricsTable(SymfonyStyle $io, array $metrics): void
    {
        $rows = [];
        foreach ($metrics as $metric) {
            $rows[] = [
                $metric->getMetricId(),
                $metric->getMetricName(),
                $metric->getValue() . (null !== $metric->getMetricUnit() ? " {$metric->getMetricUnit()}" : ''),
                null !== $metric->getCategory() ? $metric->getCategory() : '未分类',
            ];
        }

        $io->table(['ID', '名称', '值', '分类'], $rows);
    }
}
