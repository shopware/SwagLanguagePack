<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use Symfony\Component\Filesystem\Path;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS2.0' => true,
        'no_unused_imports' => true,
    ])
    ->setUsingCache(true)
    ->setCacheFile(Path::join($_SERVER['SHOPWARE_TOOL_CACHE_ECS'] ?? __DIR__, 'commercial.cache'))
    ->setFinder(
        (new Finder())
            ->in([__DIR__ . '/src', __DIR__ . '/tests'])
            ->exclude(['node_modules', '*/vendor/*'])
    );
