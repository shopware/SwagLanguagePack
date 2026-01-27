<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\Snippet\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetEntity;
use Swag\LanguagePack\PackLanguage\PackLanguageCollection;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageEntity;

/**
 * @internal
 */
readonly class CleanupReplacedLanguage
{
    /**
     * @param EntityRepository<LanguageCollection> $languageRepository
     * @param EntityRepository<SnippetSetCollection> $snippetSetRepository
     * @param EntityRepository<PackLanguageCollection> $packLanguageRepository
     * @param EntityRepository<SalesChannelDomainCollection> $salesChannelDomainRepository
     */
    public function __construct(
        private EntityRepository $languageRepository,
        private EntityRepository $snippetSetRepository,
        private EntityRepository $packLanguageRepository,
        private EntityRepository $salesChannelDomainRepository,
        private Connection $connection,
    ) {
    }

    public function changeSalesChannelDomainSnippetSet(string $locale, Context $context): void
    {
        $sourceSnippetSet = $this->getLanguagePackSnippetSetByLocale($locale, $context);
        $targetSnippetSet = $this->getBaseSnippetSetByLocale($locale, $context);

        if ($sourceSnippetSet === null || $targetSnippetSet === null) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('snippetSetId', $sourceSnippetSet->getId()));

        $salesChannelDomainIds = $this->salesChannelDomainRepository->searchIds($criteria, $context)->getIds();

        if (\count($salesChannelDomainIds) === 0) {
            return;
        }

        $this->salesChannelDomainRepository->update(
            array_map(static fn (string $id) => [
                'id' => $id,
                'snippetSetId' => $targetSnippetSet->getId(),
            ], $salesChannelDomainIds),
            $context
        );
    }

    public function removeLanguageRelation(string $locale, Context $context): void
    {
        $snippetSet = $this->getLanguagePackSnippetSetByLocale($locale, $context);

        if ($snippetSet === null) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addAssociation('locale');
        $criteria->addFilter(new EqualsFilter('locale.code', $locale));

        $language = $this->languageRepository->search($criteria, $context)->first();

        if (!$language instanceof LanguageEntity) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('languageId', $language->getId()));

        $packLanguage = $this->packLanguageRepository->search($criteria, $context)->first();

        if (!$packLanguage instanceof PackLanguageEntity) {
            return;
        }

        $this->connection->executeStatement(
            'UPDATE `' . LanguageDefinition::ENTITY_NAME . '` ' .
            'SET `' . PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME . '` = NULL ' .
            'WHERE `id` = UUID_TO_BIN(:id)',
            ['id' => $language->getId()]
        );

        $this->connection->executeStatement(
            'DELETE FROM `' . PackLanguageDefinition::ENTITY_NAME . '` WHERE `id` = UUID_TO_BIN(:id)',
            ['id' => $packLanguage->getId()]
        );
    }

    public function removeLanguagePackSnippetSet(string $locale, Context $context): void
    {
        $languagePackSnippetSet = $this->getLanguagePackSnippetSetByLocale($locale, $context);

        if ($languagePackSnippetSet === null) {
            return;
        }
        $this->snippetSetRepository->delete([['id' => $languagePackSnippetSet->getId()]], $context);
    }

    private function getLanguagePackSnippetSetByLocale(string $locale, Context $context): ?SnippetSetEntity
    {
        return $this->getSnippetSetByPrefixAndLocale('LanguagePack', $locale, $context);
    }

    private function getBaseSnippetSetByLocale(string $locale, Context $context): ?SnippetSetEntity
    {
        return $this->getSnippetSetByPrefixAndLocale('BASE', $locale, $context);
    }

    private function getSnippetSetByPrefixAndLocale(string $prefix, string $locale, Context $context): ?SnippetSetEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new AndFilter([
                new EqualsFilter('iso', $locale),
                new EqualsFilter('name', "{$prefix} {$locale}"),
            ])
        );

        return $this->snippetSetRepository->search($criteria, $context)->getEntities()->first();
    }
}
