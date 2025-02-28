<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Lifecycle;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;

class Lifecycle
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $this->updateInvalidUserLanguages();

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->deleteBaseSnippetSets();
        $this->dropConstraints();
        $this->dropTables();
        $this->dropColumns();
    }

    private function updateInvalidUserLanguages(): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
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
            SQL,
            [
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'locales' => array_values(SwagLanguagePack::SUPPORTED_LANGUAGES),
            ],
            [
                'locales' => ArrayParameterType::STRING,
            ],
        );
    }

    private function deleteBaseSnippetSets(): void
    {
        $sql = <<<'SQL'
            DELETE FROM `snippet_set`
            WHERE `name` = :name
              AND `base_file` = :baseFile;
        SQL;

        foreach (SwagLanguagePack::BASE_SNIPPET_SET_LOCALES as $locale) {
            $this->connection->executeStatement(
                $sql,
                [
                    'name' => \sprintf('LanguagePack %s', $locale),
                    'baseFile' => \sprintf('messages.%s', $locale),
                ],
            );
        }
    }

    private function dropConstraints(): void
    {
        $constraintName = 'fk.language_swag_language_pack_language';
        $replaceVariables = ['#table#', '#constraint#'];
        $dropData = [
            [LanguageDefinition::ENTITY_NAME, $constraintName],
        ];

        foreach ($dropData as $dropColumn) {
            $checkSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'SELECT *
                FROM `information_schema`.`REFERENTIAL_CONSTRAINTS`
                WHERE `CONSTRAINT_NAME` = "#constraint#"
                  AND `TABLE_NAME` = "#table#"
                  AND `CONSTRAINT_SCHEMA` = DATABASE();',
            );

            $constraintExists = (bool) $this->connection->executeQuery($checkSql)->fetchOne();

            if (!$constraintExists) {
                continue;
            }

            $dropSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'ALTER TABLE `#table#`
                    DROP FOREIGN KEY `#constraint#`;',
            );

            $this->connection->executeStatement($dropSql);
        }
    }

    private function dropTables(): void
    {
        $classNames = [
            PackLanguageDefinition::ENTITY_NAME,
        ];

        foreach ($classNames as $className) {
            $this->connection->executeStatement(\sprintf('DROP TABLE IF EXISTS `%s`', $className));
        }
    }

    private function dropColumns(): void
    {
        $replaceVariables = ['#table#', '#column#'];
        $dropData = [
            [LanguageDefinition::ENTITY_NAME, PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME],
        ];

        foreach ($dropData as $dropColumn) {
            $checkSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'SHOW COLUMNS FROM `#table#`
                LIKE "#column#";',
            );

            $columnExists = (bool) $this->connection->executeQuery($checkSql)->fetchOne();

            if (!$columnExists) {
                continue;
            }

            $dropSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'ALTER TABLE `#table#`
                        DROP COLUMN `#column#`;',
            );

            $this->connection->executeStatement($dropSql);
        }
    }
}
