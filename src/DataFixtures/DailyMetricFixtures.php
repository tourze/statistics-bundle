<?php

namespace StatisticsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use StatisticsBundle\Entity\DailyMetric;
use StatisticsBundle\Entity\DailyReport;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 统计日报指标数据填充
 */
#[When(env: 'test')]
class DailyMetricFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_METRIC_REFERENCE = 'user-metric';
    public const REVENUE_METRIC_REFERENCE = 'revenue-metric';
    public const TRAFFIC_METRIC_REFERENCE = 'traffic-metric';
    public const QUALITY_METRIC_REFERENCE = 'quality-metric';

    public function load(ObjectManager $manager): void
    {
        // 获取报告引用
        $currentReport = $this->getReference(DailyReportFixtures::CURRENT_REPORT_REFERENCE, DailyReport::class);
        $yesterdayReport = $this->getReference(DailyReportFixtures::YESTERDAY_REPORT_REFERENCE, DailyReport::class);

        // 为当前报告创建独立的指标记录
        $this->createMetricsForReport($manager, $currentReport);

        // 为昨日报告创建独立的指标记录
        $this->createMetricsForReport($manager, $yesterdayReport);

        // 创建独立的指标示例
        $this->createStandaloneMetrics($manager, $currentReport);

        $manager->flush();
    }

    /**
     * 为指定报告创建指标
     */
    private function createMetricsForReport(ObjectManager $manager, DailyReport $report): void
    {
        $metrics = $this->getMetricDefinitions();

        foreach ($metrics as $metricData) {
            $metric = new DailyMetric();
            $metric->setReport($report);
            $metric->setMetricId($metricData['id']);
            $metric->setMetricName($metricData['name']);
            $metric->setMetricUnit($metricData['unit'] ?? null);
            $metric->setCategory($metricData['category'] ?? null);
            $metric->setValue($metricData['value']);

            $manager->persist($metric);
        }
    }

    /**
     * 获取指标定义
     *
     * @return array<int, array{id: string, name: string, value: float, unit?: string, category?: string}>
     */
    private function getMetricDefinitions(): array
    {
        return [
            [
                'id' => 'new_users',
                'name' => '新增用户数',
                'value' => 85,
                'unit' => '人',
                'category' => '用户统计',
            ],
            [
                'id' => 'session_duration_avg',
                'name' => '平均会话时长',
                'value' => 12.5,
                'unit' => '分钟',
                'category' => '用户统计',
            ],
            [
                'id' => 'bounce_rate',
                'name' => '跳出率',
                'value' => 0.35,
                'unit' => '%',
                'category' => '流量统计',
            ],
            [
                'id' => 'conversion_rate',
                'name' => '转化率',
                'value' => 0.068,
                'unit' => '%',
                'category' => '业务统计',
            ],
            [
                'id' => 'server_uptime',
                'name' => '服务器正常运行时间',
                'value' => 99.95,
                'unit' => '%',
                'category' => '质量统计',
            ],
            [
                'id' => 'database_queries',
                'name' => '数据库查询次数',
                'value' => 562800,
                'unit' => '次',
                'category' => '性能统计',
            ],
            [
                'id' => 'cache_hit_rate',
                'name' => '缓存命中率',
                'value' => 0.892,
                'unit' => '%',
                'category' => '性能统计',
            ],
            [
                'id' => 'mobile_users_ratio',
                'name' => '移动端用户占比',
                'value' => 0.68,
                'unit' => '%',
                'category' => '设备统计',
            ],
            [
                'id' => 'search_queries',
                'name' => '搜索查询次数',
                'value' => 3240,
                'unit' => '次',
                'category' => '功能统计',
            ],
            [
                'id' => 'file_downloads',
                'name' => '文件下载次数',
                'value' => 1850,
                'unit' => '次',
                'category' => '功能统计',
            ],
        ];
    }

    /**
     * 创建独立的指标示例
     */
    private function createStandaloneMetrics(ObjectManager $manager, DailyReport $report): void
    {
        // 用户相关指标
        $userMetric = new DailyMetric();
        $userMetric->setReport($report);
        $userMetric->setMetricId('active_users_peak');
        $userMetric->setMetricName('高峰期活跃用户数');
        $userMetric->setMetricUnit('人');
        $userMetric->setCategory('用户统计');
        $userMetric->setValue(1580);
        $manager->persist($userMetric);
        $this->addReference(self::USER_METRIC_REFERENCE, $userMetric);

        // 收入相关指标
        $revenueMetric = new DailyMetric();
        $revenueMetric->setReport($report);
        $revenueMetric->setMetricId('subscription_revenue');
        $revenueMetric->setMetricName('订阅收入');
        $revenueMetric->setMetricUnit('元');
        $revenueMetric->setCategory('财务统计');
        $revenueMetric->setValue(15320.75);
        $manager->persist($revenueMetric);
        $this->addReference(self::REVENUE_METRIC_REFERENCE, $revenueMetric);

        // 流量相关指标
        $trafficMetric = new DailyMetric();
        $trafficMetric->setReport($report);
        $trafficMetric->setMetricId('api_requests');
        $trafficMetric->setMetricName('API请求总数');
        $trafficMetric->setMetricUnit('次');
        $trafficMetric->setCategory('流量统计');
        $trafficMetric->setValue(187500);
        $manager->persist($trafficMetric);
        $this->addReference(self::TRAFFIC_METRIC_REFERENCE, $trafficMetric);

        // 质量相关指标
        $qualityMetric = new DailyMetric();
        $qualityMetric->setReport($report);
        $qualityMetric->setMetricId('response_time_avg');
        $qualityMetric->setMetricName('平均响应时间');
        $qualityMetric->setMetricUnit('ms');
        $qualityMetric->setCategory('质量统计');
        $qualityMetric->setValue(125.8);
        $manager->persist($qualityMetric);
        $this->addReference(self::QUALITY_METRIC_REFERENCE, $qualityMetric);
    }

    public function getDependencies(): array
    {
        return [
            DailyReportFixtures::class,
        ];
    }
}
