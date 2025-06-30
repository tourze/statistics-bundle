<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration\Command;

use StatisticsBundle\Command\StatsTableCommand;
use StatisticsBundle\Tests\Integration\IntegrationTestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StatsTableCommandTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testCommandIsRegistered(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has(StatsTableCommand::class));
    }

    public function testCommandExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(StatsTableCommand::NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandName(): void
    {
        $this->assertSame('app:stats-table', StatsTableCommand::NAME);
    }
}