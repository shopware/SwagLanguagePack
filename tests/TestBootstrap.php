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
use Shopware\Core\TestBootstrapper;

if (is_readable(__DIR__ . '/../../../../src/Core/TestBootstrapper.php')) {
    require __DIR__ . '/../../../../src/Core/TestBootstrapper.php';
} else {
    require __DIR__ . '/../vendor/shopware/core/TestBootstrapper.php';
}

$classLoader = (new TestBootstrapper())
    ->setProjectDir($_SERVER['PROJECT_ROOT'] ?? dirname(__DIR__, 4))
    ->setLoadEnvFile(true)
    ->setForceInstallPlugins(true)
    ->addCallingPlugin()
    ->bootstrap()
    ->getClassLoader();

$classLoader->addPsr4('Swag\\LanguagePack\\Test\\', __DIR__);

// build StaticAnalyzeKernel container for phpstan
$testKernel = KernelLifecycleManager::getKernel();
$pluginLoader = new DbalKernelPluginLoader($classLoader, null, $testKernel->getContainer()->get(Connection::class));
$kernel = new StaticAnalyzeKernel('test', true, $pluginLoader, 'phpstan-test-cache-id');
$kernel->boot();

return $classLoader;
