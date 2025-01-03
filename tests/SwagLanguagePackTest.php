<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginDefinition;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\LanguagePack\SwagLanguagePack;

class SwagLanguagePackTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use KernelTestBehaviour;

    /**
     * @var EntityRepository<PluginCollection>
     */
    private EntityRepository $pluginRepository;

    protected function setUp(): void
    {
        /** @var EntityRepository<PluginCollection> $pluginRepository */
        $pluginRepository = $this->getContainer()->get(\sprintf('%s.repository', PluginDefinition::ENTITY_NAME));
        $this->pluginRepository = $pluginRepository;
    }

    public function testThatPluginIsInstalled(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('baseClass', SwagLanguagePack::class),
        );

        $plugin = $this->pluginRepository->search($criteria, Context::createDefaultContext())->first();

        static::assertNotNull($plugin, 'Plugin needs to be installed to run testsuite');
    }

    public function testUpdateSnippetsAndLanguagesOnUpdate(): void
    {
        $connection = $this->getContainer()->get(Connection::class);

        $lastLocale = SwagLanguagePack::SUPPORTED_LANGUAGES;
        $lastLocale = \end($lastLocale);

        $languageCountBefore = $connection->fetchOne('SELECT COUNT(*) FROM `language`');
        $swagLanguageCountBefore = $connection->fetchOne('SELECT COUNT(*) FROM `swag_language_pack_language`');

        $languageId = $connection->fetchOne(
            <<<'SQL'
                SELECT `language`.`id` FROM `locale`
                    LEFT JOIN `language` ON `language`.`locale_id` = `locale`.`id`
                    WHERE `locale`.`code` = :code
            SQL,
            ['code' => $lastLocale],
        );

        $connection->executeStatement(
            'DELETE FROM `swag_language_pack_language` WHERE `language_id` = :languageId',
            ['languageId' => $languageId],
        );

        $connection->executeStatement(
            'DELETE FROM `language` WHERE `id` = :languageId',
            ['languageId' => $languageId],
        );

        static::assertNotEquals($languageCountBefore, $connection->fetchOne('SELECT COUNT(*) FROM `language`'));
        static::assertNotEquals($swagLanguageCountBefore, $connection->fetchOne('SELECT COUNT(*) FROM `swag_language_pack_language`'));

        $pluginLifeCycleService = $this->getContainer()->get(PluginLifecycleService::class);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('baseClass', SwagLanguagePack::class));

        $context = Context::createDefaultContext();

        $plugin = $this->pluginRepository->search($criteria, $context)->first();
        static::assertInstanceOf(PluginEntity::class, $plugin);

        $pluginLifeCycleService->updatePlugin($plugin, $context);

        static::assertEquals($languageCountBefore, $connection->fetchOne('SELECT COUNT(*) FROM `language`'));
        static::assertEquals($swagLanguageCountBefore, $connection->fetchOne('SELECT COUNT(*) FROM `swag_language_pack_language`'));
    }
}
