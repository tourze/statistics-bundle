<?php

namespace StatisticsBundle\Attribute;

use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Enum\StatType;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class AsStatsColumn
{
    public function __construct(
        public StatTimeDimension $timeDimension,
        public StatType $statsType,
        public string $title,
        public ?string $name = null
    ) {
    }
}
