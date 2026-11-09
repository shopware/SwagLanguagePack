<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\TestDefaults;
use Swag\LanguagePack\Core\System\Snippet\Service\LanguagePackSnippetSetMigrator;

/**
 * @internal
 */
#[CoversClass(LanguagePackSnippetSetMigrator::class)]
class LanguagePackSnippetSetMigratorTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const LOCALE = 'zz-ZZ';

    private Connection $connection;

    private LanguagePackSnippetSetMigrator $migrator;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->migrator = new LanguagePackSnippetSetMigrator($this->connection);
    }

    public function testRenamesLanguagePackSetWhenNoBaseSetExists(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');
        $domainId = $this->createSalesChannelDomain($languagePackSetId);

        $changedSetIds = $this->migrator->migrate(self::LOCALE);

        static::assertSame([], $changedSetIds);
        static::assertSame('BASE ' . self::LOCALE, $this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(['custom.key' => 'custom value'], $this->fetchSnippets($languagePackSetId));
        static::assertSame($languagePackSetId, $this->fetchDomainSnippetSetId($domainId));
    }

    public function testMovesSnippetsAndDomainsIntoExistingBaseSetAndDeletesEmptyLanguagePackSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $baseSetId = $this->createSnippetSet('BASE');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');
        $this->createSnippet($baseSetId, 'other.key', 'other value');
        $domainId = $this->createSalesChannelDomain($languagePackSetId);

        $changedSetIds = $this->migrator->migrate(self::LOCALE);

        static::assertEqualsCanonicalizing([$languagePackSetId, $baseSetId], $changedSetIds);
        static::assertNull($this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(
            ['custom.key' => 'custom value', 'other.key' => 'other value'],
            $this->fetchSnippets($baseSetId),
        );
        static::assertSame($baseSetId, $this->fetchDomainSnippetSetId($domainId));
    }

    public function testKeepsLanguagePackSetWithConflictingSnippets(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $baseSetId = $this->createSnippetSet('BASE');
        $this->createSnippet($languagePackSetId, 'shared.key', 'language pack value');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');
        $this->createSnippet($baseSetId, 'shared.key', 'base value');

        $this->migrator->migrate(self::LOCALE);

        static::assertSame('LanguagePack ' . self::LOCALE, $this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(['shared.key' => 'language pack value'], $this->fetchSnippets($languagePackSetId));
        static::assertSame(
            ['custom.key' => 'custom value', 'shared.key' => 'base value'],
            $this->fetchSnippets($baseSetId),
        );
    }

    public function testMergesDuplicateLanguagePackSetsIntoOneBaseSet(): void
    {
        $olderSetId = $this->createSnippetSet('LanguagePack', '2020-01-01 00:00:00.000');
        $newerSetId = $this->createSnippetSet('LanguagePack', '2026-01-01 00:00:00.000');
        $this->createSnippet($olderSetId, 'older.key', 'older value');
        $this->createSnippet($newerSetId, 'newer.key', 'newer value');

        $changedSetIds = $this->migrator->migrate(self::LOCALE);

        static::assertEqualsCanonicalizing([$olderSetId, $newerSetId], $changedSetIds);
        static::assertSame('BASE ' . self::LOCALE, $this->fetchSnippetSetName($olderSetId));
        static::assertNull($this->fetchSnippetSetName($newerSetId));
        static::assertSame(
            ['newer.key' => 'newer value', 'older.key' => 'older value'],
            $this->fetchSnippets($olderSetId),
        );
    }

    public function testDoesNothingWithoutLanguagePackSet(): void
    {
        $baseSetId = $this->createSnippetSet('BASE');
        $this->createSnippet($baseSetId, 'custom.key', 'custom value');

        static::assertSame([], $this->migrator->migrate(self::LOCALE));
        static::assertSame('BASE ' . self::LOCALE, $this->fetchSnippetSetName($baseSetId));
        static::assertSame(['custom.key' => 'custom value'], $this->fetchSnippets($baseSetId));
    }

    public function testMergeIntoBaseSetKeepsLanguagePackSetWithoutBaseSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');

        static::assertSame([], $this->migrator->mergeIntoBaseSet(self::LOCALE));
        static::assertSame('LanguagePack ' . self::LOCALE, $this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(['custom.key' => 'custom value'], $this->fetchSnippets($languagePackSetId));
    }

    public function testMergeIntoBaseSetMovesSnippetsIntoExistingBaseSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $baseSetId = $this->createSnippetSet('BASE');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');

        $changedSetIds = $this->migrator->mergeIntoBaseSet(self::LOCALE);

        static::assertEqualsCanonicalizing([$languagePackSetId, $baseSetId], $changedSetIds);
        static::assertNull($this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(['custom.key' => 'custom value'], $this->fetchSnippets($baseSetId));
    }

    public function testRemoveOrMigrateDeletesUnusedLanguagePackSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');

        $this->migrator->removeOrMigrate(self::LOCALE);

        static::assertNull($this->fetchSnippetSetName($languagePackSetId));
        static::assertFalse($this->connection->fetchOne(
            'SELECT 1 FROM `snippet_set` WHERE `iso` = :iso',
            ['iso' => self::LOCALE],
        ));
    }

    public function testRemoveOrMigrateKeepsLanguagePackSetWithSnippetsAsBaseSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $this->createSnippet($languagePackSetId, 'custom.key', 'custom value');

        $this->migrator->removeOrMigrate(self::LOCALE);

        static::assertSame('BASE ' . self::LOCALE, $this->fetchSnippetSetName($languagePackSetId));
        static::assertSame(['custom.key' => 'custom value'], $this->fetchSnippets($languagePackSetId));
    }

    public function testRemoveOrMigrateKeepsLanguagePackSetUsedByDomainAsBaseSet(): void
    {
        $languagePackSetId = $this->createSnippetSet('LanguagePack');
        $domainId = $this->createSalesChannelDomain($languagePackSetId);

        $this->migrator->removeOrMigrate(self::LOCALE);

        static::assertSame('BASE ' . self::LOCALE, $this->fetchSnippetSetName($languagePackSetId));
        static::assertSame($languagePackSetId, $this->fetchDomainSnippetSetId($domainId));
    }

    private function createSnippetSet(string $prefix, string $createdAt = '2025-01-01 00:00:00.000'): string
    {
        $snippetSetId = Uuid::randomHex();

        $this->connection->insert('snippet_set', [
            'id' => Uuid::fromHexToBytes($snippetSetId),
            'name' => $prefix . ' ' . self::LOCALE,
            'base_file' => 'messages.' . self::LOCALE,
            'iso' => self::LOCALE,
            'created_at' => $createdAt,
        ]);

        return $snippetSetId;
    }

    private function createSnippet(string $snippetSetId, string $key, string $value): void
    {
        $this->connection->insert('snippet', [
            'id' => Uuid::randomBytes(),
            'snippet_set_id' => Uuid::fromHexToBytes($snippetSetId),
            'translation_key' => $key,
            'value' => $value,
            'author' => 'user/admin',
            'created_at' => '2025-01-01 00:00:00.000',
        ]);
    }

    private function createSalesChannelDomain(string $snippetSetId): string
    {
        $domainId = Uuid::randomHex();

        $this->connection->insert('sales_channel_domain', [
            'id' => Uuid::fromHexToBytes($domainId),
            'sales_channel_id' => Uuid::fromHexToBytes(TestDefaults::SALES_CHANNEL),
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'currency_id' => Uuid::fromHexToBytes(Defaults::CURRENCY),
            'snippet_set_id' => Uuid::fromHexToBytes($snippetSetId),
            'url' => 'http://localhost/' . $domainId,
            'created_at' => '2025-01-01 00:00:00.000',
        ]);

        return $domainId;
    }

    private function fetchSnippetSetName(string $snippetSetId): ?string
    {
        $name = $this->connection->fetchOne(
            'SELECT `name` FROM `snippet_set` WHERE `id` = :id',
            ['id' => Uuid::fromHexToBytes($snippetSetId)],
        );

        return \is_string($name) ? $name : null;
    }

    /**
     * @return array<string, string>
     */
    private function fetchSnippets(string $snippetSetId): array
    {
        /** @var array<string, string> $snippets */
        $snippets = $this->connection->fetchAllKeyValue(
            'SELECT `translation_key`, `value` FROM `snippet` WHERE `snippet_set_id` = :id ORDER BY `translation_key`',
            ['id' => Uuid::fromHexToBytes($snippetSetId)],
        );

        return $snippets;
    }

    private function fetchDomainSnippetSetId(string $domainId): string
    {
        $snippetSetId = $this->connection->fetchOne(
            'SELECT LOWER(HEX(`snippet_set_id`)) FROM `sales_channel_domain` WHERE `id` = :id',
            ['id' => Uuid::fromHexToBytes($domainId)],
        );
        static::assertIsString($snippetSetId);

        return $snippetSetId;
    }
}
