<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\Framework\DataAbstractionLayer\Write\Validation;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\Test\TestDefaults;
use Swag\LanguagePack\Test\Helper\ServicesTrait;

class SalesChannelValidatorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var EntityRepository<SalesChannelCollection>
     */
    private EntityRepository $salesChannelRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepository<SalesChannelCollection> $salesChannelRepository */
        $salesChannelRepository = $container->get(\sprintf('%s.repository', SalesChannelDefinition::ENTITY_NAME));
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function testCreatingASalesChannelWithADeactivatedDefaultLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $languageId = $this->prepareSalesChannelActiveForLanguageByName('Dansk', false, $context);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $languageId));
        $this->createSalesChannelWithLanguage(Uuid::randomHex(), $languageId, $context);
    }

    public function testThatUpdatingASalesChannelToADisabledSalesChannelFails(): void
    {
        $context = Context::createDefaultContext();
        $enabledLanguageId = $this->prepareSalesChannelActiveForLanguageByName('Dansk', true, $context);
        $disabledLanguageId = $this->prepareSalesChannelActiveForLanguageByName('Français', false, $context);

        $salesChannelId = Uuid::randomHex();
        $this->createSalesChannelWithLanguage($salesChannelId, $enabledLanguageId, $context);

        $criteria = new Criteria([$salesChannelId]);

        $salesChannel = $this->salesChannelRepository->search($criteria, $context)->first();
        static::assertNotNull($salesChannel);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $disabledLanguageId));
        $this->salesChannelRepository->update([[
            'id' => $salesChannelId,
            'languageId' => $disabledLanguageId,
            'languages' => [['id' => $disabledLanguageId]],
        ]], $context);
    }

    private function createSalesChannelWithLanguage(string $salesChannelId, string $languageId, Context $context): void
    {
        $this->salesChannelRepository->create([[
            'id' => $salesChannelId,
            'languageId' => $languageId,
            'languages' => [['id' => $languageId]],
            'typeId' => Defaults::SALES_CHANNEL_TYPE_STOREFRONT,
            'customerGroupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'currencyId' => Defaults::CURRENCY,
            'paymentMethodId' => $this->getValidPaymentMethodId(),
            'shippingMethodId' => $this->getValidShippingMethodId(),
            'countryId' => $this->getValidCountryId(),
            'navigationCategoryId' => $this->getValidCategoryId(),
            'accessKey' => 'S3cr3tfor3st',
            'name' => 'Test SalesChannel',
        ]], $context);
    }
}
