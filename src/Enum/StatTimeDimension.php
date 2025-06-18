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

    public function getTableNameSuffix(): string
    {
        return match($this) {
            self::DAILY_NEW, self::DAILY_TOTAL => '_daily_stats',
            self::WEEKLY_NEW, self::WEEKLY_TOTAL => '_weekly_stats',
            self::MONTHLY_NEW, self::MONTHLY_TOTAL => '_monthly_stats',
        };
    }
}
