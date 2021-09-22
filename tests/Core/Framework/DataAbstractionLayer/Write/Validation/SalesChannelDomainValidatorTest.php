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
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Swag\LanguagePack\Test\Helper\ServicesTrait;

class SalesChannelDomainValidatorTest extends TestCase
{
    use ServicesTrait;

    private EntityRepositoryInterface $salesChannelDomainRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepositoryInterface $salesChannelDomainRepository */
        $salesChannelDomainRepository = $container->get(\sprintf('%s.repository', SalesChannelDomainDefinition::ENTITY_NAME));
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    public function testCreatingASalesChannelDomainWithADeactivatedLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $languageId = $this->setSalesChannelActiveForLanguageByName('Dansk', false, $context);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $languageId));
        $this->crateSalesChannelDomain(Uuid::randomHex(), $languageId, $context);
    }

    public function testUpdatingASalesChannelDomainToADisabledLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $enabledLanguageId = $this->setSalesChannelActiveForLanguageByName('Dansk', true, $context);
        $disabledLanguageId = $this->setSalesChannelActiveForLanguageByName('Français', false, $context);

        $domainId = Uuid::randomHex();
        $this->crateSalesChannelDomain($domainId, $enabledLanguageId, $context);

        $criteria = new Criteria([$domainId]);

        /** @var SalesChannelDomainEntity|null $domain */
        $domain = $this->salesChannelDomainRepository->search($criteria, $context)->first();
        static::assertNotNull($domain);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $disabledLanguageId));
        $this->salesChannelDomainRepository->update([
            [
                'id' => $domainId,
                'languageId' => $disabledLanguageId,
            ],
        ], $context);
    }

    private function crateSalesChannelDomain(string $domainId, string $languageId, Context $context): void
    {
        $this->salesChannelDomainRepository->create([
            [
                'id' => $domainId,
                'url' => 'http://example.com',
                'salesChannelId' => Defaults::SALES_CHANNEL,
                'languageId' => $languageId,
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB'),
            ],
        ], $context);
    }
}
