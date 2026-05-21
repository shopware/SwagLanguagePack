<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Core\TestBootstrapper;

$platformRoot = dirname(__DIR__, 4);

if (is_readable($platformRoot . '/src/Core/TestBootstrapper.php')) {
    require $platformRoot . '/src/Core/TestBootstrapper.php';
} else {
    require __DIR__ . '/../vendor/shopware/core/TestBootstrapper.php';
}

$classLoader = (new TestBootstrapper())
    ->setProjectDir($_SERVER['PROJECT_ROOT'] ?? $platformRoot)
    ->setForceInstallPlugins(true)
    ->addCallingPlugin()
    ->bootstrap()
    ->getClassLoader();

/**
 * Belt-and-suspenders: re-register the plugin's PSR-4 root in case the composer
 * autoload generated during the CI `composer require` step is missing this mapping,
 * which has historically led to flaky "Class ... not found" errors in random-ordered runs.
 */
$classLoader->addPsr4('Swag\\LanguagePack\\', dirname(__DIR__) . '/src');

return $classLoader;
