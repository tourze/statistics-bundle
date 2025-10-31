<?php

declare(strict_types=1);

namespace StatisticsBundle\Service;

use Knp\Menu\ItemInterface;
use StatisticsBundle\Controller\Admin\DailyMetricCrudController;
use StatisticsBundle\Controller\Admin\DailyReportCrudController;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 统计管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建"统计管理"顶级菜单
        if (null === $item->getChild('统计管理')) {
            $item->addChild('统计管理')
                ->setAttribute('icon', 'fas fa-chart-bar')
            ;
        }

        $statsMenu = $item->getChild('统计管理');
        if (null === $statsMenu) {
            return;
        }

        // 添加日报表管理
        $statsMenu->addChild('日报表')
            ->setUri($this->linkGenerator->getCurdListPage(DailyReportCrudController::class))
            ->setAttribute('icon', 'fas fa-calendar-day')
            ->setExtra('help', '管理每日统计报表数据')
        ;

        // 添加指标管理
        $statsMenu->addChild('指标数据')
            ->setUri($this->linkGenerator->getCurdListPage(DailyMetricCrudController::class))
            ->setAttribute('icon', 'fas fa-chart-line')
            ->setExtra('help', '管理统计指标详细数据')
        ;
    }
}
