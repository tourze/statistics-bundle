<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use StatisticsBundle\DependencyInjection\StatisticsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(StatisticsExtension::class)]
final class StatisticsExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private StatisticsExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new StatisticsExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoadWithEmptyConfig(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Command\StatsTableCommand'));
        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Command\GenerateDailyReportCommand'));
        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Service\DailyReportService'));
        self::assertTrue($this->container->hasDefinition('StatisticsBundle\MessageHandler\CreateTableStatsHandler'));
    }

    public function testExtensionAlias(): void
    {
        $alias = $this->extension->getAlias();
        self::assertSame('statistics', $alias);
    }

    public function testRepositoryServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Repository\DailyReportRepository'));
        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Repository\DailyMetricRepository'));
    }

    public function testCommandServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Command\StatsTableCommand'));
        self::assertTrue($this->container->hasDefinition('StatisticsBundle\Command\GenerateDailyReportCommand'));
    }

    public function testMessageHandlerServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('StatisticsBundle\MessageHandler\CreateTableStatsHandler'));
    }
}
