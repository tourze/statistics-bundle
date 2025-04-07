<?php

namespace StatisticsBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Service\DailyReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:statistics:generate-daily-report',
    description: '生成每日统计报告',
)]
class GenerateDailyReportCommand extends Command
{
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
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制重新生成报告');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = CarbonImmutable::parse($input->getOption('date'));
        $force = $input->getOption('force');

        $io->title('生成统计日报');
        $io->section("日期: {$date->toDateString()}" . ($force ? ' (强制更新)' : ''));

        // 获取已注册的指标提供者数量
        $providers = $this->dailyReportService->getMetricProviders();
        $io->note(sprintf('已找到 %d 个指标提供者', count($providers)));

        // 生成报告
        $report = $this->dailyReportService->getDailyReport($date->toDateString());
        if ($report === null) {
            $report = new DailyReport();
            $report->setReportDate($date->toDateString());
        }

        // 计算指标
        $metrics = [];
        foreach ($this->dailyReportService->getMetricProviders() as $provider) {
            $metricId = $provider->getMetricId();
            $metric = $report->findMetric($metricId);
            if (!$metric) {
                $metric = new DailyMetric();
                $metric->setMetricId($metricId);
            }

            $metric->setReport($report);
            $metric->setMetricName($provider->getMetricName());
            $metric->setMetricUnit($provider->getMetricUnit());
            $metric->setCategory($provider->getCategory());
            $metric->setValue($provider->getMetricValue($date->toDateString()));

            $report->addMetric($metric);
            $this->entityManager->persist($metric);
            $this->entityManager->flush();
            $metrics[] = $metric;
        }

        $metricsCount = count($metrics);

        if ($metricsCount > 0) {
            $io->success(sprintf('成功生成报告，包含 %d 个指标', $metricsCount));

            // 显示指标表格
            $rows = [];
            foreach ($metrics as $metric) {
                $rows[] = [
                    $metric->getMetricId(),
                    $metric->getMetricName(),
                    $metric->getValue() . ($metric->getMetricUnit() ? " {$metric->getMetricUnit()}" : ''),
                    $metric->getCategory() ?: '未分类'
                ];
            }

            $io->table(['ID', '名称', '值', '分类'], $rows);
        } else {
            $io->warning('未生成任何指标数据');
        }

        return Command::SUCCESS;
    }
}
