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
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Language\LanguageEntity;
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
     * @param EntityRepository<PackLanguageCollection> $packLanguageRepository
     */
    public function __construct(
        private EntityRepository $languageRepository,
        private EntityRepository $packLanguageRepository,
        private Connection $connection,
    ) {
    }

    public function removeLanguageRelation(string $locale, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');
        $criteria->addFilter(new EqualsFilter('locale.code', $locale));

        $language = $this->languageRepository->search($criteria, $context)->getEntities()->first();

        if (!$language instanceof LanguageEntity) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('languageId', $language->getId()));

        $packLanguage = $this->packLanguageRepository->search($criteria, $context)->getEntities()->first();

        if (!$packLanguage instanceof PackLanguageEntity) {
            return;
        }

        $updateQuery = $this->connection->createQueryBuilder()
            ->update(LanguageDefinition::ENTITY_NAME)
            ->set(PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME, 'NULL')
            ->where('id = :id')
            ->setParameter('id', Uuid::fromHexToBytes($language->getId()));
        $updateQuery->executeQuery();

        $deleteQuery = $this->connection->createQueryBuilder()
            ->delete(PackLanguageDefinition::ENTITY_NAME)
            ->where('id = :id')
            ->setParameter('id', Uuid::fromHexToBytes($packLanguage->getId()));
        $deleteQuery->executeQuery();
    }
}
