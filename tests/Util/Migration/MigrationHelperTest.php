<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\CacheTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\FilesystemBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\RequestStackTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SessionTestBehaviour;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;
use Swag\LanguagePack\Util\Migration\MigrationHelper;

class MigrationHelperTest extends TestCase
{
    use KernelTestBehaviour;
    use DatabaseTransactionBehaviour;
    use FilesystemBehaviour;
    use CacheTestBehaviour;
    use BasicTestDataBehaviour;
    use SessionTestBehaviour;
    use RequestStackTestBehaviour;

    /**
     * @var MigrationHelper
     */
    private $migrationHelper;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $this->connection = $connection;

        $this->connection->rollback();

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
        static::assertEquals(2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertEquals(0, $counts[PackLanguageDefinition::ENTITY_NAME]);

        $this->migrationHelper->createPackLanguages();

        $counts = $this->getTableCounts($tables);
        static::assertEquals(\count(SwagLanguagePack::SUPPORTED_LANGUAGES) + 2, $counts[LanguageDefinition::ENTITY_NAME]);
        static::assertEquals(\count(SwagLanguagePack::SUPPORTED_LANGUAGES), $counts[PackLanguageDefinition::ENTITY_NAME]);
    }

    public function testMissingLocaleWhileCreating(): void
    {
        $locales = SwagLanguagePack::SUPPORTED_LANGUAGES;
        $locales['nonsense'] = 'non-sense';
        $locales['doesntExist'] = 'doesnt-exist';

        /** @var Connection $connectionMock */
        $connectionMock = $this->prepareLocaleConnectionMock($locales);

        $this->migrationHelper->createPackLanguageTable();
        $this->migrationHelper->alterLanguageAddPackLanguageColumn();

        $this->expectExceptionMessage('No LocaleEntities associated to the following locale codes: bg-BG, bs-BA, cs-CZ, da-DK, el-GR, es-ES, fi-FI, fr-FR, hi-IN, hr-HR, hu-HU, id-ID, it-IT, ko-KR, lv-LV, nl-NL, nn-NO, pl-PL, pt-PT, ro-MD, ru-RU, sk-SK, sl-SI, sr-RS, sv-SE, tr-TR, uk-UA, vi-VN');

        $mockedMigrationHelper = new MigrationHelper($connectionMock);
        $mockedMigrationHelper->createPackLanguages();
    }

    public function testCreateSnippetSetsCorrectly(): void
    {
        static::assertFalse($this->databaseHasBaseSnippetSetsForPackLanguages());

        $this->migrationHelper->createSnippetSets();
        static::assertTrue($this->databaseHasBaseSnippetSetsForPackLanguages());
    }

    private function uninstallPluginAndDeleteLanguages(): void
    {
        /** @var MockObject|UninstallContext $uninstallContext */
        $uninstallContext = $this->getMockBuilder(UninstallContext::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['keepUserData'])
            ->getMock();

        $uninstallContext
            ->expects(static::once())
            ->method('keepUserData')
            ->willReturn(false);

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        (new Lifecycle($this->connection, $languageRepository))
            ->uninstall($uninstallContext);

        $sql = \str_replace(
            ['#table#'],
            [LanguageDefinition::ENTITY_NAME],
            'DELETE FROM `#table#`
            WHERE `name` NOT IN ("Deutsch", "English");'
        );

        $this->connection->executeStatement($sql);

        $deleteSnippetSetsSql = <<<SQL
DELETE FROM `snippet_set` WHERE `name` NOT IN ("BASE de-DE", "BASE en-GB");
SQL;
        $this->connection->executeStatement($deleteSnippetSetsSql);
    }

    private function databaseHasPackLanguageTable(): bool
    {
        $sql = \str_replace(
            ['#table#'],
            [PackLanguageDefinition::ENTITY_NAME],
            'SELECT *
            FROM `information_schema`.`TABLES`
            WHERE `TABLE_NAME` = "#table#"
            AND `TABLE_SCHEMA` = DATABASE();'
        );

        return (bool) $this->connection->executeQuery($sql)->fetch();
    }

    private function doesLanguageColumnExist(): bool
    {
        $sql = \str_replace(
            ['#table#', '#column#'],
            [LanguageDefinition::ENTITY_NAME, PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME],
            'SHOW COLUMNS FROM `#table#`
                LIKE "#column#";'
        );

        return (bool) $this->connection->executeQuery($sql)->fetch();
    }

    private function getTableCounts(array $tables): array
    {
        $results = [];
        foreach ($tables as $table) {
            $sql = \str_replace(
                ['#table#'],
                [$table],
                'SELECT COUNT(*) as `count`
                FROM `#table#`;'
            );

            $results[$table] = (int) $this->connection->executeQuery($sql)->fetchColumn();
        }

        return $results;
    }

    /**
     * @return MockObject|Connection
     */
    private function prepareLocaleConnectionMock(array $locales)
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultStatementMock = $this->getMockBuilder(ResultStatement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultStatementMock
            ->expects(static::once())
            ->method('fetchAll')
            ->willReturn($locales);

        $connectionMock
            ->expects(static::once())
            ->method('executeQuery')
            ->willReturn($resultStatementMock);

        return $connectionMock;
    }

    private function databaseHasBaseSnippetSetsForPackLanguages(): bool
    {
        $sql = <<<SQL
SELECT COUNT(DISTINCT `id`) FROM `snippet_set`;
SQL;
        $snippetSetCount = (int) $this->connection->executeQuery($sql)->fetch(FetchMode::COLUMN);

        // de-DE & en-GB are system default languages so we have to add 2
        return $snippetSetCount === (\count(SwagLanguagePack::SUPPORTED_LANGUAGES) + 2);
    }
}
