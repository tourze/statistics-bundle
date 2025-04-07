<?php

namespace StatisticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use StatisticsBundle\Entity\DailyMetric;

/**
 * @method DailyMetric|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyMetric|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyMetric[] findAll()
 * @method DailyMetric[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyMetricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyMetric::class);
    }

    /**
     * 查找指定报表的所有指标
     *
     * @param int $reportId 报表ID
     * @return DailyMetric[]
     */
    public function findByReportId(int $reportId): array
    {
        return $this->createQueryBuilder('dm')
            ->where('dm.report = :reportId')
            ->setParameter('reportId', $reportId)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定报表的特定指标
     */
    public function findByReportAndMetricId(int $reportId, string $metricId): ?DailyMetric
    {
        return $this->createQueryBuilder('dm')
            ->where('dm.report = :reportId')
            ->andWhere('dm.metricId = :metricId')
            ->setParameter('reportId', $reportId)
            ->setParameter('metricId', $metricId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 按指标ID分组获取多个报表的指标值
     *
     * @param array $reportIds 报表ID数组
     * @return array 格式: ['metric_id' => ['report_id' => 'value', ...], ...]
     */
    public function getMetricValuesForReports(array $reportIds): array
    {
        if (empty($reportIds)) {
            return [];
        }

        $metrics = $this->createQueryBuilder('dm')
            ->where('dm.report IN (:reportIds)')
            ->setParameter('reportIds', $reportIds)
            ->getQuery()
            ->getResult();

        $result = [];
        /** @var DailyMetric $metric */
        foreach ($metrics as $metric) {
            $metricId = $metric->getMetricId();
            $reportId = $metric->getReport()->getId();

            if (!isset($result[$metricId])) {
                $result[$metricId] = [];
            }

            $result[$metricId][$reportId] = $metric->getValue();
        }

        return $result;
    }
}
