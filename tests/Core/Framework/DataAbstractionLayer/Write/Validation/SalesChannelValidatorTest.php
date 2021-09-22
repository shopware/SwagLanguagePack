<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\Framework\DataAbstractionLayer\Write\Validation;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Swag\LanguagePack\Test\Helper\ServicesTrait;

class SalesChannelValidatorTest extends TestCase
{
    use ServicesTrait;

    private EntityRepositoryInterface $salesChannelRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $container->get(\sprintf('%s.repository', SalesChannelDefinition::ENTITY_NAME));
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function testCreatingASalesChannelWithADeactivatedDefaultLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $languageId = $this->setSalesChannelActiveForLanguageByName('Dansk', false, $context);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $languageId));
        $this->createSalesChannelWithLanguage(Uuid::randomHex(), $languageId, $context);
    }

    public function testThatUpdatingASalesChannelToADisabledSalesChannelFails(): void
    {
        $context = Context::createDefaultContext();
        $enabledLanguageId = $this->setSalesChannelActiveForLanguageByName('Dansk', true, $context);
        $disabledLanguageId = $this->setSalesChannelActiveForLanguageByName('Français', false, $context);

        $salesChannelId = Uuid::randomHex();
        $this->createSalesChannelWithLanguage($salesChannelId, $enabledLanguageId, $context);

        $criteria = new Criteria([$salesChannelId]);

        /** @var SalesChannelEntity|null $salesChannel */
        $salesChannel = $this->salesChannelRepository->search($criteria, $context)->first();
        static::assertNotNull($salesChannel);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $disabledLanguageId));
        $this->salesChannelRepository->update([
            [
                'id' => $salesChannelId,
                'languageId' => $disabledLanguageId,
                'languages' => [['id' => $disabledLanguageId]],
            ],
        ], $context);
    }

    private function createSalesChannelWithLanguage(string $salesChannelId, string $languageId, Context $context): void
    {
        $this->salesChannelRepository->create([
            [
                'id' => $salesChannelId,
                'languageId' => $languageId,
                'languages' => [['id' => $languageId]],
                'typeId' => Defaults::SALES_CHANNEL_TYPE_STOREFRONT,
                'customerGroupId' => Defaults::FALLBACK_CUSTOMER_GROUP,
                'currencyId' => Defaults::CURRENCY,
                'paymentMethodId' => $this->getValidPaymentMethodId(),
                'shippingMethodId' => $this->getValidShippingMethodId(),
                'countryId' => $this->getValidCountryId(),
                'navigationCategoryId' => $this->getValidCategoryId(),
                'accessKey' => 'S3cr3tfor3st',
                'name' => 'Test SalesChannel',
            ],
        ], $context);
    }
}
