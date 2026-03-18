<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/tests',
    ]);

    // Auto-import names
    $rectorConfig->importNames(true, true);
    $rectorConfig->importShortClasses();
    $rectorConfig->removeUnusedImports();

    // Basic sets
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PHP_84,
        PHPUnitSetList::PHPUNIT_100, // Converts test_methods to #[Test]
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        LaravelSetList::LARAVEL_110,
    ]);
};
