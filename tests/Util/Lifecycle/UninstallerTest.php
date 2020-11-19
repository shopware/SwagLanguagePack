<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Test\Cart\Promotion\Helpers\Fakes\FakeResultStatement;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Migration\MigrationCollection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Lifecycle\Uninstaller;

class UninstallerTest extends TestCase
{
    public function testUninstallWithoutKeepingUserData(): void
    {
        $connection = $this->getConnectionMock();

        $connection->expects(static::atLeast(4))
            ->method('executeUpdate')
            ->willReturnOnConsecutiveCalls([false, true]);

        $connection->expects(static::atLeast(2))
            ->method('executeQuery')
            ->willReturn(new FakeResultStatement([]));

        $uninstaller = new Uninstaller($connection);
        $uninstaller->uninstall($this->getUninstallContext());
    }

    public function testUninstallKeepsUserData(): void
    {
        $connection = $this->getConnectionMock();
        $uninstallContext = $this->getUninstallContext(true);

        $connection->expects(static::never())
            ->method('executeUpdate');

        $uninstaller = new Uninstaller($connection);
        $uninstaller->uninstall($uninstallContext);
    }

    private function getUninstallContext(bool $keepUserData = false): UninstallContext
    {
        return new UninstallContext(
            new SwagLanguagePack(true, ''),
            Context::createDefaultContext(),
            '',
            '',
            $this->createMock(MigrationCollection::class),
            $keepUserData
        );
    }

    /**
     * @return Connection|MockObject
     */
    private function getConnectionMock()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeUpdate', 'executeQuery'])
            ->getMock();
    }
}
