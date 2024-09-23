<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\Extension\LanguageExtension;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Exception\LanguagePackException;

class Lifecycle
{
    /**
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $languageRepository,
    ) {}

    /**
     * @deprecated tag:v4.0.0 - Will be removed without replacement
     */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $criteria = (new Criteria())->addFilter(
            new MultiFilter('AND', [
                new NotFilter('AND', [
                    new EqualsFilter('salesChannels.id', null),
                ]),
                new NotFilter('AND', [
                    new EqualsFilter(
                        \sprintf('%s.id', LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME),
                        null,
                    ),
                ]),
            ]),
        )->addSorting(new FieldSorting('name', 'ASC'));

        $result = $this->languageRepository->search($criteria, $deactivateContext->getContext());

        if ($result->getTotal() > 0) {
            /** @var LanguageCollection $languages */
            $languages = $result->getEntities();

            throw LanguagePackException::packLanguagesStillInUse($languages);
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->deleteBaseSnippetSets();
        $this->dropConstraints();
        $this->dropTables();
        $this->dropColumns();
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
