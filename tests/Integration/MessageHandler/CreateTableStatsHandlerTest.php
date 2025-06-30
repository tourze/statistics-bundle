<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Integration\MessageHandler;

use StatisticsBundle\MessageHandler\CreateTableStatsHandler;
use StatisticsBundle\Tests\Integration\IntegrationTestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateTableStatsHandlerTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testHandlerIsRegistered(): void
    {
        $container = self::getContainer();
        $this->assertTrue($container->has(CreateTableStatsHandler::class));
    }

    public function testHandlerInstantiation(): void
    {
        $container = self::getContainer();
        $handler = $container->get(CreateTableStatsHandler::class);
        
        $this->assertInstanceOf(CreateTableStatsHandler::class, $handler);
    }
}