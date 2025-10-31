<?php

namespace StatisticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use StatisticsBundle\Contract\DailyReportStorageInterface;
use StatisticsBundle\Entity\DailyReport;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<DailyReport>
 */
#[AsRepository(entityClass: DailyReport::class)]
class DailyReportRepository extends ServiceEntityRepository implements DailyReportStorageInterface
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
        $result = $this->createQueryBuilder('dr')
            ->where('dr.reportDate >= :startDate')
            ->andWhere('dr.reportDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('dr.reportDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));
        /** @var DailyReport[] $result */

        return $result;
    }

    public function save(DailyReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DailyReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
