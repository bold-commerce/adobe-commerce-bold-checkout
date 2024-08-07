<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__,
    ])
    ->withSets([
        DowngradeLevelSetList::DOWN_TO_PHP_72
    ]);
