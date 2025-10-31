<?php

declare(strict_types=1);

namespace StatisticsBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置代码
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $adminMenu = self::getService(AdminMenu::class);

        self::assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testIsReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);

        self::assertTrue($reflection->isReadOnly());
    }
}
