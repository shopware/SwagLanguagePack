<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\Content;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;

class PackLanguageRepositoryDecorator implements EntityRepositoryInterface
{
    private EntityRepositoryInterface $inner;

    public function __construct(EntityRepositoryInterface $inner)
    {
        $this->inner = $inner;
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $clonedCriteria = clone $criteria;
        $clonedCriteria->addAssociation('language.locale');

        return $this->inner->search($clonedCriteria, $context);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        $clonedCriteria = clone $criteria;
        $clonedCriteria->addAssociation('language.locale');

        return $this->inner->searchIds($clonedCriteria, $context);
    }

    /*
     * Unchanged methods
     */
    public function getDefinition(): EntityDefinition
    {
        return $this->inner->getDefinition();
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        return $this->inner->aggregate($criteria, $context);
    }

    public function clone(string $id, Context $context, ?string $newId = null, ?CloneBehavior $behavior = null): EntityWrittenContainerEvent
    {
        return $this->inner->clone($id, $context, $newId, $behavior);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->inner->update($data, $context);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->inner->upsert($data, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->inner->create($data, $context);
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        return $this->inner->delete($ids, $context);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->inner->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->inner->merge($versionId, $context);
    }
}
