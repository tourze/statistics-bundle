<?php

namespace StatisticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use StatisticsBundle\Entity\DailyMetric;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<DailyMetric>
 */
#[AsRepository(entityClass: DailyMetric::class)]
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
     *
     * @return DailyMetric[]
     */
    public function findByReportId(int $reportId): array
    {
        $result = $this->createQueryBuilder('dm')
            ->where('dm.report = :reportId')
            ->setParameter('reportId', $reportId)
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));
        /** @var DailyMetric[] $result */

        return $result;
    }

    /**
     * 查找指定报表的特定指标
     */
    public function findByReportAndMetricId(int $reportId, string $metricId): ?DailyMetric
    {
        $result = $this->createQueryBuilder('dm')
            ->where('dm.report = :reportId')
            ->andWhere('dm.metricId = :metricId')
            ->setParameter('reportId', $reportId)
            ->setParameter('metricId', $metricId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof DailyMetric || null === $result);

        return $result;
    }

    /**
     * 按指标ID分组获取多个报表的指标值
     *
     * @param int[] $reportIds 报表ID数组
     *
     * @return array<string, array<int|string, float>> 格式: ['metric_id' => ['report_id' => 'value', ...], ...]
     */
    public function getMetricValuesForReports(array $reportIds): array
    {
        if (0 === count($reportIds)) {
            return [];
        }

        $metricsResult = $this->createQueryBuilder('dm')
            ->where('dm.report IN (:reportIds)')
            ->setParameter('reportIds', $reportIds)
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($metricsResult));
        $metrics = $metricsResult;

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

    public function save(DailyMetric $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DailyMetric $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
