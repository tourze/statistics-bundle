<?php

namespace StatisticsBundle\Enum;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum StatType: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case SUM = 'sum';
    case COUNT = 'count';
    case AVG = 'avg';

    public function getLabel(): string
    {
        return match($this) {
            self::SUM => '总和',
            self::COUNT => '计数',
            self::AVG => '平均值',
        };
    }
}
