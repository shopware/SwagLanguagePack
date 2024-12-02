<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Helper;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\LanguagePack\PackLanguage\PackLanguageCollection;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

trait ServicesTrait
{
    use IntegrationTestBehaviour;

    private function prepareSalesChannelActiveForLanguageByName(string $name, bool $salesChannelActive, Context $context): string
    {
        /** @var EntityRepository<PackLanguageCollection> $packLanguageRepository */
        $packLanguageRepository = $this->getContainer()->get(\sprintf('%s.repository', PackLanguageDefinition::ENTITY_NAME));

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('language.name', $name));

        $first = $packLanguageRepository->search($criteria, $context)->getEntities()->first();
        static::assertNotNull($first);

        $packLanguageRepository->update([[
            'id' => $first->getId(),
            'salesChannelActive' => $salesChannelActive,
        ]], $context);

        return $first->getLanguageId();
    }

    private function prepareAdministrationActiveForLanguageByLocale(string $locale, bool $administrationActive, Context $context): string
    {
        /** @var EntityRepository<PackLanguageCollection> $packLanguageRepository */
        $packLanguageRepository = $this->getContainer()->get(\sprintf('%s.repository', PackLanguageDefinition::ENTITY_NAME));

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('language.locale.code', $locale));

        $first = $packLanguageRepository->search($criteria, $context)->getEntities()->first();
        static::assertNotNull($first);

        $packLanguageRepository->update([[
            'id' => $first->getId(),
            'administrationActive' => $administrationActive,
        ]], $context);

        return $first->getLanguage()->getLocaleId();
    }
}
