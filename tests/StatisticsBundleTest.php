<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests;

use PHPUnit\Framework\TestCase;
use StatisticsBundle\StatisticsBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StatisticsBundleTest extends TestCase
{
    private StatisticsBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new StatisticsBundle();
    }

    public function test_extendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function test_bundleInstantiation(): void
    {
        $this->assertInstanceOf(StatisticsBundle::class, $this->bundle);
    }

    public function test_bundleName(): void
    {
        $expected = 'StatisticsBundle';
        $this->assertSame($expected, $this->bundle->getName());
    }

    public function test_bundleNamespace(): void
    {
        $expected = 'StatisticsBundle';
        $this->assertSame($expected, $this->bundle->getNamespace());
    }

    public function test_bundlePath(): void
    {
        $path = $this->bundle->getPath();
        $this->assertStringEndsWith('statistics-bundle/src', $path);
    }
} 