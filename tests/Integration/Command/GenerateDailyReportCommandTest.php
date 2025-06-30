<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration\Command;

use StatisticsBundle\Command\GenerateDailyReportCommand;
use StatisticsBundle\Tests\Integration\DatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateDailyReportCommandTest extends DatabaseTestCase
{
    public function testCommandIsRegistered(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has(GenerateDailyReportCommand::class));
    }

    public function testCommandExecuteWithDefaults(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(GenerateDailyReportCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
    }

    public function testCommandExecuteWithDateOption(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(GenerateDailyReportCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--date' => '2024-01-15',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
        $this->assertStringContainsString('2024-01-15', $output);
    }

    public function testCommandExecuteWithForceOption(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(GenerateDailyReportCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--force' => true,
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('生成统计日报', $output);
        $this->assertStringContainsString('强制更新', $output);
    }

    public function testCommandExecuteWithBothOptions(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(GenerateDailyReportCommand::NAME);
        $commandTester = new CommandTester($command);

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