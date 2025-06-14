<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use StatisticsBundle\Command\GenerateDailyReportCommand;
use StatisticsBundle\Command\StatsTableCommand;
use StatisticsBundle\Service\DailyReportService;
use StatisticsBundle\StatisticsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class StatisticsBundleIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected static function createKernel(array $options = []): IntegrationTestKernel
    {
        $appendBundles = [
            FrameworkBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            StatisticsBundle::class => ['all' => true],
        ];
        
        $entityMappings = [];

        return new IntegrationTestKernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true,
            $appendBundles,
            $entityMappings
        );
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testServiceWiring(): void
    {
        $container = self::getContainer();

        // 测试命令服务是否正确注册
        $this->assertTrue($container->has(StatsTableCommand::class));
        $this->assertTrue($container->has(GenerateDailyReportCommand::class));

        // 测试核心服务是否正确注册
        $this->assertTrue($container->has(DailyReportService::class));

        // 测试服务实例化
        $this->assertInstanceOf(
            DailyReportService::class,
            $container->get(DailyReportService::class)
        );
    }
}
