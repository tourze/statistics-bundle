<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Command\GenerateDailyReportCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(GenerateDailyReportCommand::class)]
#[RunTestsInSeparateProcesses]
final class GenerateDailyReportCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 空实现，满足抽象方法要求
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(GenerateDailyReportCommand::class);

        return new CommandTester($command);
    }

    public function testOptionDate(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--date' => '2024-01-15',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
        $this->assertStringContainsString('2024-01-15', $output);
    }

    public function testOptionForce(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--force' => true,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
        $this->assertStringContainsString('强制更新', $output);
    }

    public function testCommandExecuteWithDefaults(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
    }

    public function testCommandExecuteWithBothOptions(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--date' => '2024-01-15',
            '--force' => true,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
        $this->assertStringContainsString('2024-01-15', $output);
        $this->assertStringContainsString('强制更新', $output);
    }
}
