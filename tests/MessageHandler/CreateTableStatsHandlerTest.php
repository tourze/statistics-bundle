<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\MessageHandler\CreateTableStatsHandler;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CreateTableStatsHandler::class)]
#[RunTestsInSeparateProcesses]
final class CreateTableStatsHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 空实现，满足抽象方法要求
    }

    public function testHandlerInstantiation(): void
    {
        // 从容器中获取处理器实例，确保依赖正确注入
        $handler = self::getService(CreateTableStatsHandler::class);

        $this->assertInstanceOf(CreateTableStatsHandler::class, $handler);
    }
}
