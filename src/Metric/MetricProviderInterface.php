<?php

namespace StatisticsBundle\Metric;

use Carbon\CarbonImmutable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * 指标提供者接口
 *
 * 该接口用于定义能够提供统计指标数据的服务
 */
#[AutoconfigureTag(self::SERVICE_TAG)]
interface MetricProviderInterface
{
    const SERVICE_TAG = 'statistics.metric';

    /**
     * 获取指标的唯一标识符
     */
    public function getMetricId(): string;

    /**
     * 获取指标的显示名称
     */
    public function getMetricName(): string;

    /**
     * 获取指标的描述
     */
    public function getMetricDescription(): string;

    /**
     * 获取指标的单位
     */
    public function getMetricUnit(): string;

    /**
     * 获取指标所属分类ID
     */
    public function getCategory(): string;

    /**
     * 获取分类的显示顺序
     */
    public function getCategoryOrder(): int;

    /**
     * 获取指定日期的指标值
     *
     * @param CarbonImmutable $date 日期
     * @return mixed 指标值
     */
    public function getMetricValue(CarbonImmutable $date): mixed;
}
