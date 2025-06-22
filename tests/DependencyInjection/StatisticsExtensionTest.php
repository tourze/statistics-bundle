<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\DependencyInjection\StatisticsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class StatisticsExtensionTest extends TestCase
{
    private StatisticsExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new StatisticsExtension();
        $this->container = new ContainerBuilder();
    }

    public function test_extendsSymfonyExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function test_load_withEmptyConfig(): void
    {
        $configs = [];
        
        $this->extension->load($configs, $this->container);
        
        // 验证服务配置已加载
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Command\StatsTableCommand'));
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Command\GenerateDailyReportCommand'));
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Service\DailyReportService'));
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\MessageHandler\CreateTableStatsHandler'));
    }

    public function test_load_withValidConfig(): void
    {
        $configs = [
            [
                // 可以添加配置选项
            ]
        ];
        
        $this->extension->load($configs, $this->container);
        
        // 验证服务仍然被正确注册
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Service\DailyReportService'));
    }

    public function test_extensionAlias(): void
    {
        $alias = $this->extension->getAlias();
        $this->assertSame('statistics', $alias);
    }

    public function test_servicesAreAutowired(): void
    {
        $this->extension->load([], $this->container);
        
        // 检查服务是否设置为自动装配
        $serviceDefinitions = $this->container->getDefinitions();
        
        foreach ($serviceDefinitions as $definition) {
            if ($definition->getClass() !== null && str_starts_with($definition->getClass(), 'StatisticsBundle\\')) {
                $this->assertTrue($definition->isAutowired(), 
                    'Service ' . $definition->getClass() . ' should be autowired');
            }
        }
    }

    public function test_servicesAreAutoconfigured(): void
    {
        $this->extension->load([], $this->container);
        
        // 检查服务是否设置为自动配置
        $serviceDefinitions = $this->container->getDefinitions();
        
        foreach ($serviceDefinitions as $definition) {
            if ($definition->getClass() !== null && str_starts_with($definition->getClass(), 'StatisticsBundle\\')) {
                $this->assertTrue($definition->isAutoconfigured(), 
                    'Service ' . $definition->getClass() . ' should be autoconfigured');
            }
        }
    }

    public function test_repositoryServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Repository\DailyReportRepository'));
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Repository\DailyMetricRepository'));
    }

    public function test_commandServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Command\StatsTableCommand'));
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\Command\GenerateDailyReportCommand'));
    }

    public function test_messageHandlerServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition('StatisticsBundle\MessageHandler\CreateTableStatsHandler'));
    }

    public function test_serviceResourcePathsAreCorrect(): void
    {
        $this->extension->load([], $this->container);
        
        // 检查资源路径是否正确配置
        $definitions = $this->container->getDefinitions();
        $commandFound = false;
        $serviceFound = false;
        
        foreach ($definitions as $definition) {
            if ($definition->getClass() !== null) {
                if (str_contains($definition->getClass(), 'Command\\')) {
                    $commandFound = true;
                }
                if (str_contains($definition->getClass(), 'Service\\')) {
                    $serviceFound = true;
                }
            }
        }
        
        $this->assertTrue($commandFound, 'Command services should be registered');
        $this->assertTrue($serviceFound, 'Service classes should be registered');
    }
} 