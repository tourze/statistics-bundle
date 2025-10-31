<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Entity\DailyReport;
use StatisticsBundle\Repository\DailyReportRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(DailyReportRepository::class)]
#[RunTestsInSeparateProcesses]
final class DailyReportRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 只删除 DailyMetric，保留 DailyReport 的 DataFixtures 数据
        self::getEntityManager()->createQuery('DELETE FROM StatisticsBundle\Entity\DailyMetric')->execute();
        self::getEntityManager()->flush();
    }

    protected function createNewEntity(): object
    {
        $entity = new DailyReport();
        $entity->setReportDate('2024-01-' . rand(10, 28));

        return $entity;
    }

    protected function getRepository(): DailyReportRepository
    {
        return self::getService(DailyReportRepository::class);
    }

    public function testRepositoryIsRegistered(): void
    {
        $this->assertInstanceOf(DailyReportRepository::class, $this->getRepository());
    }

    public function testFindByDate(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findByDate('2024-01-15');

        $this->assertInstanceOf(DailyReport::class, $result);
        $this->assertSame('2024-01-15', $result->getReportDate());
    }

    public function testFindByDateNotFound(): void
    {
        $result = $this->getRepository()->findByDate('2024-01-15');

        $this->assertNull($result);
    }

    public function testFindByDateRange(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');

        $report3 = new DailyReport();
        $report3->setReportDate('2024-01-20');

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->persist($report3);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findByDateRange('2024-01-15', '2024-01-17');

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DailyReport::class, $result);
        $this->assertSame('2024-01-15', $result[0]->getReportDate());
        $this->assertSame('2024-01-16', $result[1]->getReportDate());
    }

    public function testFindByDateRangeEmpty(): void
    {
        $result = $this->getRepository()->findByDateRange('2024-01-15', '2024-01-17');

        $this->assertEmpty($result);
    }

    public function testFindByDateRangeSingleDay(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findByDateRange('2024-01-15', '2024-01-15');

        $this->assertCount(1, $result);
        $this->assertSame('2024-01-15', $result[0]->getReportDate());
    }

    public function testSave(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $this->getRepository()->save($report);

        $savedReport = $this->getRepository()->find($report->getId());
        $this->assertInstanceOf(DailyReport::class, $savedReport);
        $this->assertSame('2024-01-15', $savedReport->getReportDate());
    }

    public function testSaveWithoutFlush(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        $this->getRepository()->save($report, false);
        self::getEntityManager()->flush();

        $savedReport = $this->getRepository()->find($report->getId());
        $this->assertInstanceOf(DailyReport::class, $savedReport);
        $this->assertSame('2024-01-15', $savedReport->getReportDate());
    }

    public function testRemove(): void
    {
        $report = new DailyReport();
        $report->setReportDate('2024-01-15');

        self::getEntityManager()->persist($report);
        self::getEntityManager()->flush();

        $reportId = $report->getId();
        $this->getRepository()->remove($report);

        $removedReport = $this->getRepository()->find($reportId);
        $this->assertNull($removedReport);
    }

    public function testFindByWithNullableFields(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');
        $report1->setExtraData(['type' => 'daily']);

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->flush();

        $nullExtraDataReports = $this->getRepository()->findBy(['extraData' => null]);
        $this->assertCount(1, $nullExtraDataReports);
        $this->assertSame('2024-01-16', $nullExtraDataReports[0]->getReportDate());
    }

    public function testCountWithNullableFields(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');
        $report1->setExtraData(['type' => 'daily']);

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->flush();

        $nullExtraDataCount = $this->getRepository()->count(['extraData' => null]);
        $this->assertSame(1, $nullExtraDataCount);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        // DataFixtures 已经包含了一些数据，我们只需要验证排序功能正常工作
        $earliestReport = $this->getRepository()->findOneBy([], ['reportDate' => 'ASC']);
        $this->assertInstanceOf(DailyReport::class, $earliestReport);

        $latestReport = $this->getRepository()->findOneBy([], ['reportDate' => 'DESC']);
        $this->assertInstanceOf(DailyReport::class, $latestReport);

        // 验证最早的日期确实早于最新的日期
        $earliestDate = new \DateTime($earliestReport->getReportDate());
        $latestDate = new \DateTime($latestReport->getReportDate());
        $this->assertLessThanOrEqual($latestDate, $earliestDate);
    }

    public function testFindByWithNullExtraDataField(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');
        $report1->setExtraData(['type' => 'daily']);

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');
        // extraData 为 null

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->flush();

        // 测试查找 extraData 为 null 的记录
        $nullExtraDataReports = $this->getRepository()->findBy(['extraData' => null]);
        $this->assertCount(1, $nullExtraDataReports);
        $this->assertSame('2024-01-16', $nullExtraDataReports[0]->getReportDate());

        // 测试计数查询中的 null 值
        $nullExtraDataCount = $this->getRepository()->count(['extraData' => null]);
        $this->assertSame(1, $nullExtraDataCount);
    }

    public function testCountWithNullExtraDataField(): void
    {
        $report1 = new DailyReport();
        $report1->setReportDate('2024-01-15');
        $report1->setExtraData(['type' => 'daily']);

        $report2 = new DailyReport();
        $report2->setReportDate('2024-01-16');
        // extraData 为 null

        $report3 = new DailyReport();
        $report3->setReportDate('2024-01-17');
        // extraData 也为 null

        self::getEntityManager()->persist($report1);
        self::getEntityManager()->persist($report2);
        self::getEntityManager()->persist($report3);
        self::getEntityManager()->flush();

        // 测试计数 extraData 为 null 的记录
        // DataFixtures 中的所有记录都有 extraData，所以只有我们新创建的 2 条记录为 null
        $nullExtraDataCount = $this->getRepository()->count(['extraData' => null]);
        $this->assertSame(2, $nullExtraDataCount);

        // 测试查找 extraData 不为 null 的记录总数
        // DataFixtures 创建了一些记录，加上我们创建的 1 条非 null 记录
        $totalReports = $this->getRepository()->count([]);
        $nonNullCount = $totalReports - $nullExtraDataCount;
        // 验证至少有 DataFixtures 创建的记录（应该大于 0）
        $this->assertGreaterThan(0, $nonNullCount);
    }
}
