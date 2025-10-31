<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\StatisticsBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(StatisticsBundle::class)]
#[RunTestsInSeparateProcesses]
final class StatisticsBundleTest extends AbstractBundleTestCase
{
}
