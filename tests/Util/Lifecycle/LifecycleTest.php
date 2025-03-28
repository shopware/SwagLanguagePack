<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Lifecycle;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Migration\MigrationCollection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;

class LifecycleTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testUninstallWithoutKeepingUserData(): void
    {
        $connection = $this->getConnectionMock();

        $connection->expects(static::atLeast(5))
            ->method('executeStatement');

        $connection->expects(static::atLeast(2))
            ->method('executeQuery');

        $lifecycle = new Lifecycle($connection);
        $lifecycle->uninstall($this->getUninstallContext());
    }

    public function testUninstallKeepsUserData(): void
    {
        $connection = $this->getConnectionMock();
        $uninstallContext = $this->getUninstallContext(true);

        $expectedSql = <<<'SQL'
            UPDATE `user`
            SET `locale_id` = (
                SELECT `locale_id`
                FROM `language`
                WHERE `id` = UNHEX(:languageId)
            )
            WHERE `locale_id` IN (
                SELECT `id`
                FROM `locale`
                WHERE `code` IN (:locales)
            );
        SQL;

        $connection->expects(static::once())
            ->method('executeStatement')
            ->with(
                $expectedSql,
                [
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'locales' => array_values(SwagLanguagePack::SUPPORTED_LANGUAGES),
                ],
                [
                    'locales' => ArrayParameterType::STRING,
                ],
            );

        $lifecycle = new Lifecycle($connection);
        $lifecycle->uninstall($uninstallContext);
    }

    private function getUninstallContext(bool $keepUserData = false): UninstallContext
    {
        return new UninstallContext(
            new SwagLanguagePack(true, ''),
            Context::createDefaultContext(),
            '',
            '',
            $this->createMock(MigrationCollection::class),
            $keepUserData,
        );
    }

    /**
     * @return Connection|MockObject
     */
    private function getConnectionMock()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeStatement', 'executeQuery'])
            ->getMock();
    }
}
