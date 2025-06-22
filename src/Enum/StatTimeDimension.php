<?php

namespace StatisticsBundle\Enum;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum StatTimeDimension: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case DAILY_NEW = 'daily_new';
    case WEEKLY_NEW = 'weekly_new';
    case MONTHLY_NEW = 'monthly_new';
    case DAILY_TOTAL = 'daily_total';
    case WEEKLY_TOTAL = 'weekly_total';
    case MONTHLY_TOTAL = 'monthly_total';

    public function getLabel(): string
    {
        return match($this) {
            self::DAILY_NEW => '每日新增',
            self::WEEKLY_NEW => '每周新增',
            self::MONTHLY_NEW => '每月新增',
            self::DAILY_TOTAL => '每日总量',
            self::WEEKLY_TOTAL => '每周总量',
            self::MONTHLY_TOTAL => '每月总量',
        };
    }

    public function getTableNameSuffix(): string
    {
        return match($this) {
            self::DAILY_NEW, self::DAILY_TOTAL => '_daily_stats',
            self::WEEKLY_NEW, self::WEEKLY_TOTAL => '_weekly_stats',
            self::MONTHLY_NEW, self::MONTHLY_TOTAL => '_monthly_stats',
        };
    }
}
