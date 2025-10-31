<?php

namespace StatisticsBundle\Contract;

use StatisticsBundle\Entity\DailyReport;

/**
 * DailyReport 仓储接口
 */
interface DailyReportStorageInterface
{
    /**
     * 根据日期查找日报
     */
    public function findByDate(string $date): ?DailyReport;

    /**
     * 查询指定日期范围内的日报
     *
     * @return DailyReport[]
     */
    public function findByDateRange(string $startDate, string $endDate): array;

    /**
     * 保存实体
     */
    public function save(DailyReport $entity, bool $flush = true): void;

    /**
     * 删除实体
     */
    public function remove(DailyReport $entity, bool $flush = true): void;
}
