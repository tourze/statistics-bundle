<?php

namespace StatisticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use StatisticsBundle\Entity\DailyReport;

/**
 * @method DailyReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyReport[] findAll()
 * @method DailyReport[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyReport::class);
    }

    /**
     * 根据日期查找日报
     */
    public function findByDate(string $date): ?DailyReport
    {
        return $this->findOneBy(['reportDate' => $date]);
    }

    /**
     * 查询指定日期范围内的日报
     */
    public function findByDateRange(string $startDate, string $endDate): array
    {
        return $this->createQueryBuilder('dr')
            ->where('dr.reportDate >= :startDate')
            ->andWhere('dr.reportDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('dr.reportDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
