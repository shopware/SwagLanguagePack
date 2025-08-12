<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\CacheTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\FilesystemBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\RequestStackTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SessionTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;
use Swag\LanguagePack\Util\Migration\MigrationHelper;

class MigrationHelperTest extends TestCase
{
    use BasicTestDataBehaviour;
    use CacheTestBehaviour;
    use DatabaseTransactionBehaviour;
    use FilesystemBehaviour;
    use KernelTestBehaviour;
    use RequestStackTestBehaviour;
    use SessionTestBehaviour;

    private MigrationHelper $migrationHelper;

    private Connection $connection;

    protected function setUp(): void
    {
        $connection = $this->getContainer()->get(Connection::class);
        $this->connection = $connection;

        $this->connection->rollBack();

        $this->migrationHelper = new MigrationHelper($this->connection);
        $this->uninstallPluginAndDeleteLanguages();
    }

    protected function tearDown(): void
    {
        $this->migrationHelper->createPackLanguageTable();
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();
        $this->migrationHelper->createPackLanguages();
        $this->migrationHelper->createSnippetSets();

        $this->connection->beginTransaction();
    }

    public function testCreateLanguageTableCorrectly(): void
    {
        static::assertFalse($this->databaseHasPackLanguageTable());

        $this->migrationHelper->createPackLanguageTable();
        static::assertTrue($this->databaseHasPackLanguageTable());
    }

    public function testDontAlterLanguageTableBecauseColumnExists(): void
    {
        $this->migrationHelper->createPackLanguageTable();
        static::assertFalse($this->doesLanguageColumnExist());

        $this->migrationHelper->alterLanguageAddPackLanguageColumn();
        static::assertTrue($this->doesLanguageColumnExist());

        // Check again if working correctly, even if column exists
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();
        static::assertTrue($this->doesLanguageColumnExist());
    }

    public function testCreatePackLanguagesCorrectly(): void
    {
        $tables = [PackLanguageDefinition::ENTITY_NAME, LanguageDefinition::ENTITY_NAME];
        $this->migrationHelper->createPackLanguageTable();
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();

        $counts = $this->getTableCounts($tables);
        static::assertSame(2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertSame(0, $counts[PackLanguageDefinition::ENTITY_NAME]);

        $this->migrationHelper->createPackLanguages();

        $counts = $this->getTableCounts($tables);
        static::assertSame(\count(SwagLanguagePack::SUPPORTED_LANGUAGES) + 2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertSame(\count(SwagLanguagePack::SUPPORTED_LANGUAGES), $counts[PackLanguageDefinition::ENTITY_NAME]);
    }

    public function testCreatePackLanguagesCorrectlyEvenWithAlreadyInstalledPackLanguages(): void
    {
        $tables = [PackLanguageDefinition::ENTITY_NAME, LanguageDefinition::ENTITY_NAME];
        $this->migrationHelper->createPackLanguageTable();
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();

        $counts = $this->getTableCounts($tables);
        static::assertSame(2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertSame(0, $counts[PackLanguageDefinition::ENTITY_NAME]);

        // Simulate already having one language installed
        $this->createMockPackLanguage();
        $counts = $this->getTableCounts($tables);
        static::assertSame(3, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertSame(1, $counts[PackLanguageDefinition::ENTITY_NAME]);

        $this->migrationHelper->createPackLanguages();

        $counts = $this->getTableCounts($tables);
        static::assertSame(\count(SwagLanguagePack::SUPPORTED_LANGUAGES) + 2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertSame(\count(SwagLanguagePack::SUPPORTED_LANGUAGES), $counts[PackLanguageDefinition::ENTITY_NAME]);
    }

    public function testMissingLocaleWhileCreating(): void
    {
        $locales = SwagLanguagePack::SUPPORTED_LANGUAGES;
        $locales['nonsense'] = 'non-sense';
        $locales['doesntExist'] = 'doesnt-exist';

        $connectionMock = $this->prepareLocaleConnectionMock($locales);

        $this->migrationHelper->createPackLanguageTable();
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();

        $this->expectExceptionMessage('No LocaleEntities associated to the following locale codes: bg-BG, bs-BA, cs-CZ, da-DK, el-GR, en-US, es-ES, fi-FI, fr-FR, hi-IN, hr-HR, hu-HU, id-ID, it-IT, ko-KR, lv-LV, nl-NL, nn-NO, pl-PL, pt-PT, ro-RO, ru-RU, sk-SK, sl-SI, sr-RS, sv-SE, tr-TR, uk-UA, vi-VN');

        $mockedMigrationHelper = new MigrationHelper($connectionMock);
        $mockedMigrationHelper->createPackLanguages();
    }

    public function testCreateSnippetSetsCorrectly(): void
    {
        static::assertFalse($this->databaseHasBaseSnippetSetsForPackLanguages());

        $this->migrationHelper->createSnippetSets();
        static::assertTrue($this->databaseHasBaseSnippetSetsForPackLanguages());

        // test again for duplicates, for example when plugin update is triggered
        $this->migrationHelper->createSnippetSets();
        static::assertTrue($this->databaseHasBaseSnippetSetsForPackLanguages());
    }

    private function uninstallPluginAndDeleteLanguages(): void
    {
        $uninstallContext = $this->getMockBuilder(UninstallContext::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['keepUserData'])
            ->getMock();

        $uninstallContext
            ->expects(static::once())
            ->method('keepUserData')
            ->willReturn(false);

        /** @var EntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        (new Lifecycle($this->connection, $languageRepository))
            ->uninstall($uninstallContext);

        $sql = \str_replace(
            ['#table#'],
            [LanguageDefinition::ENTITY_NAME],
            <<<'SQL'
                DELETE FROM `#table#`
                WHERE `name` NOT IN ("Deutsch", "English");
            SQL,
        );

        $this->connection->executeStatement($sql);

        $deleteSnippetSetsSql = <<<'SQL'
            DELETE FROM `snippet_set`
            WHERE `name` NOT IN ("BASE de-DE", "BASE en-GB");
        SQL;
        $this->connection->executeStatement($deleteSnippetSetsSql);
    }

    private function databaseHasPackLanguageTable(): bool
    {
        $sql = \str_replace(
            ['#table#'],
            [PackLanguageDefinition::ENTITY_NAME],
            <<<'SQL'
                SELECT *
                FROM `information_schema`.`TABLES`
                WHERE `TABLE_NAME` = "#table#"
                AND `TABLE_SCHEMA` = DATABASE();
            SQL,
        );

        return (bool) $this->connection->executeQuery($sql)->fetchOne();
    }

    private function doesLanguageColumnExist(): bool
    {
        $sql = \str_replace(
            ['#table#', '#column#'],
            [LanguageDefinition::ENTITY_NAME, PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME],
            <<<'SQL'
                SHOW COLUMNS FROM `#table#`
                LIKE "#column#";
            SQL,
        );

        return (bool) $this->connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param array<int, string> $tables
     *
     * @return array<string, int>
     */
    private function getTableCounts(array $tables): array
    {
        $results = [];
        foreach ($tables as $table) {
            $sql = \str_replace(
                ['#table#'],
                [$table],
                <<<'SQL'
                    SELECT COUNT(*) as `count`
                    FROM `#table#`;
                SQL,
            );

            $results[$table] = (int) $this->connection->executeQuery($sql)->fetchOne();
        }

        return $results;
    }

    /**
     * @param array<string, string> $locales
     */
    private function prepareLocaleConnectionMock(array $locales): Connection&MockObject
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultStatementMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultStatementMock
            ->expects(static::once())
            ->method('fetchAllAssociative')
            ->willReturn($locales);

        $connectionMock
            ->expects(static::once())
            ->method('executeQuery')
            ->willReturn($resultStatementMock);

        return $connectionMock;
    }

    private function databaseHasBaseSnippetSetsForPackLanguages(): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(DISTINCT `id`)
            FROM `snippet_set`;
        SQL;
        $snippetSetCount = (int) $this->connection->executeQuery($sql)->fetchOne();

        // de-DE & en-GB are system default languages so we have to add 2
        return $snippetSetCount === (\count(SwagLanguagePack::SUPPORTED_LANGUAGES) + 2);
    }

    private function createMockPackLanguage(): void
    {
        $localeId = $this->connection->executeQuery(<<<'SQL'
            SELECT `locale`.`id` AS `locale_id`
            FROM `locale`
            LEFT JOIN `language` ON `locale`.`id` = `language`.`locale_id`
            WHERE `code` = 'fr-FR';
        SQL)->fetchOne();

        $insertLanguagesSql = <<<'SQL'
            INSERT INTO `language` (`id`, `name`, `locale_id`, `translation_code_id`, `created_at`)
            VALUES (:id, :name, :localeId, :translationCodeId, NOW());
        SQL;

        $languageId = Uuid::randomBytes();
        $this->connection->executeStatement($insertLanguagesSql, [
            'id' => $languageId,
            'name' => 'Français',
            'localeId' => $localeId,
            'translationCodeId' => $localeId,
        ]);

        $insertPackLanguagesSql = <<<'SQL'
            INSERT INTO `swag_language_pack_language` (`id`, `language_id`, `administration_active`, `sales_channel_active`, `created_at`)
            VALUES (:id, :languageId, :administrationActive, :salesChannelActive, NOW());

            UPDATE `language`
            SET swag_language_pack_language_id = :id
            WHERE `id` = :languageId;
        SQL;

        $this->connection->executeStatement($insertPackLanguagesSql, [
            'id' => Uuid::randomBytes(),
            'languageId' => $languageId,
            'administrationActive' => 1,
            'salesChannelActive' => 1,
        ]);
    }
}
