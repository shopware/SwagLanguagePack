<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;
use Shopware\Core\DevOps\StaticAnalyze\StaticAnalyzeKernel;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\DbalKernelPluginLoader;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/** @var \Shopware\Core\TestBootstrapper $testBootstrapper */
$testBootstrapper = require_once __DIR__ . '/TestBootstrap.php';

// build StaticAnalyzeKernel container
$testKernel = KernelLifecycleManager::getKernel();
$pluginLoader = new DbalKernelPluginLoader(
    $testBootstrapper->getClassLoader(),
    null,
    $testKernel->getContainer()->get(Connection::class)
);
$kernel = new StaticAnalyzeKernel(
    'test',
    true,
    $pluginLoader,
    'phpstan-test-cache-id'
);

return new Application($kernel);
