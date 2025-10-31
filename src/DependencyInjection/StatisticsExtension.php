<?php

namespace StatisticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class StatisticsExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        parent::load($configs, $container);

        // 在测试环境中，不需要在这里处理冲突，已经在 Kernel 中处理了
    }
}
