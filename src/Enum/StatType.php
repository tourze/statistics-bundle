<?php

namespace StatisticsBundle\Enum;

enum StatType: string
{
    case SUM = 'sum';
    case COUNT = 'count';
    case AVG = 'avg';
}
