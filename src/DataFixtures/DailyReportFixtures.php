<?php

namespace StatisticsBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use StatisticsBundle\Entity\DailyReport;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 统计日报数据填充
 */
#[When(env: 'test')]
class DailyReportFixtures extends Fixture
{
    public const CURRENT_REPORT_REFERENCE = 'current-report';
    public const YESTERDAY_REPORT_REFERENCE = 'yesterday-report';
    public const LAST_WEEK_REPORT_REFERENCE = 'last-week-report';

    public function load(ObjectManager $manager): void
    {
        // 创建今日报告
        $currentReport = $this->createCurrentDayReport();
        $manager->persist($currentReport);
        $this->addReference(self::CURRENT_REPORT_REFERENCE, $currentReport);

        // 创建昨日报告
        $yesterdayReport = $this->createYesterdayReport();
        $manager->persist($yesterdayReport);
        $this->addReference(self::YESTERDAY_REPORT_REFERENCE, $yesterdayReport);

        // 创建上周报告
        $lastWeekReport = $this->createLastWeekReport();
        $manager->persist($lastWeekReport);
        $this->addReference(self::LAST_WEEK_REPORT_REFERENCE, $lastWeekReport);

        // 创建历史报告 (最近30天)
        $this->createHistoricalReports($manager);

        $manager->flush();
    }

    /**
     * 创建当前日期的报告
     */
    private function createCurrentDayReport(): DailyReport
    {
        $report = new DailyReport();
        $report->setReportDate(date('Y-m-d'));

        // 添加测试指标数据
        $report->addMetrics([
            'daily_users' => [
                'name' => '日活跃用户数',
                'value' => 1250,
                'unit' => '人',
                'category' => '用户统计',
            ],
            'daily_revenue' => [
                'name' => '日收入',
                'value' => 38750.50,
                'unit' => '元',
                'category' => '财务统计',
            ],
            'page_views' => [
                'name' => '页面访问量',
                'value' => 45600,
                'unit' => '次',
                'category' => '流量统计',
            ],
            'error_rate' => [
                'name' => '错误率',
                'value' => 0.025,
                'unit' => '%',
                'category' => '质量统计',
            ],
        ]);

        $report->setExtraData([
            'source' => 'system_generated',
            'version' => '1.0.0',
            'notes' => '自动生成的测试数据',
        ]);

        return $report;
    }

    /**
     * 创建昨日报告
     */
    private function createYesterdayReport(): DailyReport
    {
        $report = new DailyReport();
        $yesterday = CarbonImmutable::now()->subDay();
        $report->setReportDate($yesterday->format('Y-m-d'));

        // 添加测试指标数据
        $report->addMetrics([
            'daily_users' => [
                'name' => '日活跃用户数',
                'value' => 1180,
                'unit' => '人',
                'category' => '用户统计',
            ],
            'daily_revenue' => [
                'name' => '日收入',
                'value' => 35420.80,
                'unit' => '元',
                'category' => '财务统计',
            ],
            'page_views' => [
                'name' => '页面访问量',
                'value' => 42300,
                'unit' => '次',
                'category' => '流量统计',
            ],
            'error_rate' => [
                'name' => '错误率',
                'value' => 0.032,
                'unit' => '%',
                'category' => '质量统计',
            ],
        ]);

        $report->setExtraData([
            'source' => 'system_generated',
            'version' => '1.0.0',
            'comparison_target' => 'current_day',
        ]);

        return $report;
    }

    /**
     * 创建上周同期报告
     */
    private function createLastWeekReport(): DailyReport
    {
        $report = new DailyReport();
        $lastWeek = CarbonImmutable::now()->subWeek();
        $report->setReportDate($lastWeek->format('Y-m-d'));

        // 添加测试指标数据
        $report->addMetrics([
            'daily_users' => [
                'name' => '日活跃用户数',
                'value' => 980,
                'unit' => '人',
                'category' => '用户统计',
            ],
            'daily_revenue' => [
                'name' => '日收入',
                'value' => 28900.00,
                'unit' => '元',
                'category' => '财务统计',
            ],
            'page_views' => [
                'name' => '页面访问量',
                'value' => 35200,
                'unit' => '次',
                'category' => '流量统计',
            ],
            'error_rate' => [
                'name' => '错误率',
                'value' => 0.048,
                'unit' => '%',
                'category' => '质量统计',
            ],
        ]);

        $report->setExtraData([
            'source' => 'system_generated',
            'version' => '1.0.0',
            'comparison_target' => 'week_over_week',
        ]);

        return $report;
    }

    /**
     * 创建历史报告数据
     */
    private function createHistoricalReports(ObjectManager $manager): void
    {
        $baseDate = CarbonImmutable::now()->subDays(30);

        for ($i = 0; $i < 27; ++$i) { // 排除已创建的3天
            $date = $baseDate->addDays($i);

            // 跳过已创建的日期
            if (in_array($date->format('Y-m-d'), [
                CarbonImmutable::now()->format('Y-m-d'),
                CarbonImmutable::now()->subDay()->format('Y-m-d'),
                CarbonImmutable::now()->subWeek()->format('Y-m-d'),
            ], true)) {
                continue;
            }

            $report = new DailyReport();
            $report->setReportDate($date->format('Y-m-d'));

            // 生成波动的测试数据
            $userCount = rand(800, 1500);
            $revenue = rand(20000, 50000) + (rand(0, 99) / 100);
            $pageViews = rand(25000, 60000);
            $errorRate = rand(1, 80) / 1000; // 0.001 to 0.080

            $report->addMetrics([
                'daily_users' => [
                    'name' => '日活跃用户数',
                    'value' => $userCount,
                    'unit' => '人',
                    'category' => '用户统计',
                ],
                'daily_revenue' => [
                    'name' => '日收入',
                    'value' => $revenue,
                    'unit' => '元',
                    'category' => '财务统计',
                ],
                'page_views' => [
                    'name' => '页面访问量',
                    'value' => $pageViews,
                    'unit' => '次',
                    'category' => '流量统计',
                ],
                'error_rate' => [
                    'name' => '错误率',
                    'value' => $errorRate,
                    'unit' => '%',
                    'category' => '质量统计',
                ],
            ]);

            $report->setExtraData([
                'source' => 'system_generated',
                'version' => '1.0.0',
                'data_type' => 'historical',
            ]);

            $manager->persist($report);
        }
    }
}
