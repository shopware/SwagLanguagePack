<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\Snippet\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * `snippet.snippet_set_id` cascades on delete, so a set is only ever deleted once it is empty.
 * Uses plain SQL, because the plugin's uninstall runs when its services are no longer in the container.
 */
readonly class LanguagePackSnippetSetMigrator
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return list<string> hex ids of the snippet sets whose snippets changed
     */
    public function migrate(string $locale): array
    {
        return $this->migrateLanguagePackSets($locale, true);
    }

    /**
     * Leaves the `LanguagePack <locale>` set as it is when no `BASE <locale>` set exists, so the plugin keeps serving that locale.
     *
     * @return list<string> hex ids of the snippet sets whose snippets changed
     */
    public function mergeIntoBaseSet(string $locale): array
    {
        return $this->migrateLanguagePackSets($locale, false);
    }

    public function removeOrMigrate(string $locale): void
    {
        foreach ($this->fetchSnippetSetIds('LanguagePack', $locale) as $languagePackSetId) {
            $this->deleteIfUnused($languagePackSetId);
        }

        $this->migrate($locale);
    }

    /**
     * @return list<string>
     */
    private function migrateLanguagePackSets(string $locale, bool $renameWithoutBaseSet): array
    {
        return $this->connection->transactional(function () use ($locale, $renameWithoutBaseSet): array {
            $changedSetIds = [];

            foreach ($this->fetchSnippetSetIds('LanguagePack', $locale) as $languagePackSetId) {
                $baseSetId = $this->fetchSnippetSetIds('BASE', $locale)[0] ?? null;

                if ($baseSetId === null) {
                    if ($renameWithoutBaseSet) {
                        $this->connection->update(
                            'snippet_set',
                            ['name' => self::snippetSetName('BASE', $locale)],
                            ['id' => $languagePackSetId],
                        );
                    }

                    continue;
                }

                $this->moveSnippets($languagePackSetId, $baseSetId);
                $this->moveSalesChannelDomains($languagePackSetId, $baseSetId);
                $this->deleteIfUnused($languagePackSetId);

                $changedSetIds[] = Uuid::fromBytesToHex($languagePackSetId);
                $changedSetIds[] = Uuid::fromBytesToHex($baseSetId);
            }

            return array_values(array_unique($changedSetIds));
        });
    }

    /**
     * Moves only the snippets whose key the target set does not have yet, because
     * `uniq.snippet_set_id_translation_key` rejects duplicates.
     */
    private function moveSnippets(string $sourceSetId, string $targetSetId): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                UPDATE `snippet` AS `source`
                LEFT JOIN `snippet` AS `target`
                    ON `target`.`snippet_set_id` = :targetSetId
                    AND `target`.`translation_key` = `source`.`translation_key`
                SET `source`.`snippet_set_id` = :targetSetId,
                    `source`.`updated_at` = NOW(3)
                WHERE `source`.`snippet_set_id` = :sourceSetId
                  AND `target`.`id` IS NULL;
            SQL,
            [
                'sourceSetId' => $sourceSetId,
                'targetSetId' => $targetSetId,
            ],
        );
    }

    private function moveSalesChannelDomains(string $sourceSetId, string $targetSetId): void
    {
        $this->connection->update(
            'sales_channel_domain',
            ['snippet_set_id' => $targetSetId],
            ['snippet_set_id' => $sourceSetId],
        );
    }

    /**
     * One statement, so a snippet saved between a separate check and the delete cannot be cascaded away.
     */
    private function deleteIfUnused(string $snippetSetId): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                DELETE FROM `snippet_set`
                WHERE `id` = :id
                  AND NOT EXISTS(SELECT 1 FROM `snippet` WHERE `snippet_set_id` = :id)
                  AND NOT EXISTS(SELECT 1 FROM `sales_channel_domain` WHERE `snippet_set_id` = :id);
            SQL,
            ['id' => $snippetSetId],
        );
    }

    /**
     * @return list<string> binary ids, oldest first
     */
    private function fetchSnippetSetIds(string $prefix, string $locale): array
    {
        /** @var list<string> $snippetSetIds */
        $snippetSetIds = $this->connection->fetchFirstColumn(
            <<<'SQL'
                SELECT `id`
                FROM `snippet_set`
                WHERE `name` = :name
                  AND `iso` = :iso
                ORDER BY `created_at`, `id`;
            SQL,
            [
                'name' => self::snippetSetName($prefix, $locale),
                'iso' => $locale,
            ],
        );

        return $snippetSetIds;
    }

    private static function snippetSetName(string $prefix, string $locale): string
    {
        return \sprintf('%s %s', $prefix, $locale);
    }
}
