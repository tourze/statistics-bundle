<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Command\StatsTableCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(StatsTableCommand::class)]
#[RunTestsInSeparateProcesses]
final class StatsTableCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 空实现，满足抽象方法要求
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(StatsTableCommand::class);

        return new CommandTester($command);
    }

    public function testCommandExecute(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandName(): void
    {
        $this->assertSame('app:stats-table', StatsTableCommand::NAME);
    }
}
