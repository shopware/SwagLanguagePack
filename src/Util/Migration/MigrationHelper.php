<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Migration;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Exception\LanguagePackException;

class MigrationHelper
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function createPackLanguageTable(): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS `#table#` (
                `id`                    BINARY(16)  NOT NULL,
                `administration_active` TINYINT(1)  NULL DEFAULT '0',
                `sales_channel_active`  TINYINT(1)  NULL DEFAULT '0',
                `language_id`           BINARY(16)  NOT NULL,
                `created_at`            DATETIME(3) NOT NULL,
                `updated_at`            DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.swag_language_pack_language_language`
                    FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`)
                    ON DELETE RESTRICT
                    ON UPDATE CASCADE
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->connection->executeStatement(\str_replace(
            ['#table#'],
            [PackLanguageDefinition::ENTITY_NAME],
            $sql,
        ));
    }

    public function alterLanguageAddPackLanguageColumn(): void
    {
        if ($this->languageColumnAlreadyExists()) {
            return;
        }

        $sql = <<<'SQL'
            ALTER TABLE `#table#`
            ADD COLUMN `#column#` BINARY(16) NULL AFTER `parent_id`,
            ADD CONSTRAINT `fk.language_swag_language_pack_language`
            FOREIGN KEY (`#column#`) REFERENCES `#pack_language_table#` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        SQL;

        $this->connection->executeStatement(\str_replace(
            ['#table#', '#column#', '#pack_language_table#'],
            [
                LanguageDefinition::ENTITY_NAME,
                PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME,
                PackLanguageDefinition::ENTITY_NAME,
            ],
            $sql,
        ));
    }

    public function createPackLanguages(): void
    {
        $locales = $this->getLocales();
        $data = $this->createPackLanguageData($locales);

        $packLanguages = [];
        $languages = [];

        /** @var array<string, string|null> $locale */
        foreach ($data as $locale) {
            if ($locale['languageId'] === null) {
                $newLanguageId = Uuid::randomBytes();
                $locale['languageId'] = $newLanguageId;

                $languages[] = [
                    'id' => $newLanguageId,
                    'name' => $locale['name'],
                    'localeId' => $locale['id'],
                    'translationCodeId' => $locale['id'],
                ];
            }

            $packLanguage = [
                'id' => Uuid::randomBytes(),
                'languageId' => $locale['languageId'],
                'administrationActive' => 1,
                'salesChannelActive' => 1,
            ];

            $packLanguages[] = $packLanguage;
        }

        $insertLanguagesSql = <<<'SQL'
            INSERT INTO `language` (`id`, `name`, `locale_id`, `translation_code_id`, `created_at`)
            VALUES (:id, :name, :localeId, :translationCodeId, NOW());
        SQL;

        foreach ($languages as $language) {
            $this->connection->executeStatement($insertLanguagesSql, $language);
        }

        $insertPackLanguagesSql = <<<'SQL'
            INSERT INTO `swag_language_pack_language` (`id`, `language_id`, `administration_active`, `sales_channel_active`, `created_at`)
            VALUES (:id, :languageId, :administrationActive, :salesChannelActive, NOW());

            UPDATE `language`
            SET swag_language_pack_language_id = :id
            WHERE `id` = :languageId;
        SQL;

        foreach ($packLanguages as $packLanguage) {
            $this->connection->executeStatement($insertPackLanguagesSql, $packLanguage);
        }
    }

    public function createSnippetSets(): void
    {
        $sql = <<<'SQL'
            SELECT `id`, `iso`, `name`
            FROM `snippet_set`
            WHERE (`name` LIKE 'BASE%' OR `name` LIKE 'LanguagePack%')
              AND `iso` IN (:isos);
        SQL;

        $existingSnippetSets = $this->connection->executeQuery(
            $sql,
            [
                'isos' => SwagLanguagePack::BASE_SNIPPET_SET_LOCALES,
            ],
            [
                'isos' => ArrayParameterType::STRING,
            ],
        )->fetchAllAssociative();

        $existingIsos = [];
        foreach ($existingSnippetSets as $snippetSet) {
            $existingIsos[] = $snippetSet['iso'];
            $snippetSetName = \sprintf('LanguagePack %s', $snippetSet['iso']);

            if ($snippetSet['name'] === $snippetSetName) {
                continue;
            }

            $this->connection->update(
                'snippet_set',
                [
                    'name' => $snippetSetName,
                ],
                [
                    'id' => $snippetSet['id'],
                ],
            );
        }

        $insertSnippetSetSql = <<<'SQL'
            INSERT INTO `snippet_set` (`id`, `name`, `base_file`, `iso`, `created_at`)
            VALUES (:id, :name, :baseFile, :iso, NOW())
        SQL;

        foreach (SwagLanguagePack::BASE_SNIPPET_SET_LOCALES as $locale) {
            if (\in_array($locale, $existingIsos, true)) {
                continue;
            }

            $this->connection->executeStatement($insertSnippetSetSql, [
                'id' => Uuid::randomBytes(),
                'name' => \sprintf('LanguagePack %s', $locale),
                'baseFile' => \sprintf('messages.%s', $locale),
                'iso' => $locale,
            ]);
        }
    }

    private function languageColumnAlreadyExists(): bool
    {
        $sql = <<<'SQL'
            SHOW COLUMNS FROM `#table#` LIKE '#column#';
        SQL;

        $result = $this->connection->executeQuery(\str_replace(
            ['#table#', '#column#'],
            [
                LanguageDefinition::ENTITY_NAME,
                PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME,
            ],
            $sql,
        ));

        return (bool) $result->fetchOne();
    }

    /**
     * @throws LanguagePackException
     *
     * @return array<string, array<string, mixed>>
     */
    private function getLocales(): array
    {
        $requiredLocales = $this->connection->executeQuery(
            <<<'SQL'
                SELECT `id`, `code`
                FROM `locale`
                WHERE `code` IN (?)
            SQL,
            [\array_values(SwagLanguagePack::SUPPORTED_LANGUAGES)],
            [ArrayParameterType::STRING],
        )->fetchAllAssociative();

        if (\count(SwagLanguagePack::SUPPORTED_LANGUAGES) !== \count($requiredLocales)) {
            throw LanguagePackException::installWithoutLocales($this->getMissingLocales($requiredLocales));
        }

        $alreadyInstalledLocales = $this->connection->executeQuery(
            <<<'SQL'
                SELECT `locale`.`code`
                FROM `locale`
                JOIN `language` ON `language`.`locale_id` = `locale`.`id`
                JOIN `swag_language_pack_language` pack_language ON pack_language.`language_id` = `language`.`id`
            SQL,
            [\array_values(SwagLanguagePack::SUPPORTED_LANGUAGES)],
            [ArrayParameterType::STRING],
        )->fetchAllAssociative();

        $languageNames = array_flip(SwagLanguagePack::SUPPORTED_LANGUAGES);

        return array_reduce($requiredLocales, static function (array $accumulator, array $requiredLocale) use ($alreadyInstalledLocales, $languageNames) {
            if (!\in_array($requiredLocale['code'], \array_column($alreadyInstalledLocales, 'code'), true)) {
                $currentCode = $requiredLocale['code'];

                $accumulator[$currentCode] = array_merge(
                    $requiredLocale,
                    ['name' => $languageNames[$currentCode]],
                );
            }

            return $accumulator;
        }, []);
    }

    /**
     * @param array<string|int, array<string, mixed>> $locales
     *
     * @return array<string|int, string>
     */
    private function getMissingLocales(array $locales): array
    {
        $supportedLanguages = SwagLanguagePack::SUPPORTED_LANGUAGES;
        \asort($supportedLanguages);

        return \array_diff(
            $supportedLanguages,
            \array_column($locales, 'code'),
        );
    }

    /**
     * @param array<string|int, array<string, mixed>> $locales
     *
     * @return array<string|int, array<string, mixed>|string>
     */
    private function createPackLanguageData(array $locales): array
    {
        $sql = <<<'SQL'
            SELECT lang.`id` as id, loc.`code` as code
            FROM `language` lang
            LEFT JOIN `locale` loc ON loc.`id` = lang.`translation_code_id`
            WHERE loc.`code` IN (?)
        SQL;

        $existingLanguages = $this->connection->executeQuery(
            $sql,
            [\array_keys($locales)],
            [ArrayParameterType::STRING],
        )->fetchAllAssociative();

        return \array_map(static function (array $locale) use ($existingLanguages): array {
            $languageId = null;
            foreach ($existingLanguages as $language) {
                if (isset($locale['code'], $language['code']) && $locale['code'] === $language['code']) {
                    $languageId = $language['id'];

                    break;
                }
            }
            $locale['languageId'] = $languageId;

            return $locale;
        }, $locales);
    }
}
