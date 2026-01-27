<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use Shopware\Core\Test\TestDefaults;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\Extension\LanguageExtension;
use Swag\LanguagePack\PackLanguage\PackLanguageCollection;

#[CoversClass(CleanupReplacedLanguage::class)]
class CleanupReplacedLanguageIntegrationTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<LanguageCollection>
     */
    protected EntityRepository $languageRepository;

    /**
     * @var EntityRepository<SnippetSetCollection>
     */
    protected EntityRepository $snippetSetRepository;

    /**
     * @var EntityRepository<LocaleCollection>
     */
    protected EntityRepository $localeRepository;

    /**
     * @var EntityRepository<PackLanguageCollection>
     */
    protected EntityRepository $packLanguageRepository;

    /**
     * @var EntityRepository<SalesChannelDomainCollection>
     */
    private EntityRepository $salesChannelDomainRepository;

    private Context $context;

    private string $languageId;

    private string $locale;

    protected function setUp(): void
    {
        $this->languageRepository = $this->getContainer()->get('language.repository');
        $this->snippetSetRepository = $this->getContainer()->get('snippet_set.repository');
        $this->localeRepository = $this->getContainer()->get('locale.repository');
        $this->packLanguageRepository = $this->getContainer()->get('swag_language_pack_language.repository');
        $this->salesChannelDomainRepository = $this->getContainer()->get('sales_channel_domain.repository');

        $this->context = Context::createDefaultContext();

        $this->locale = 'es-ES';
        $localeId = $this->createLocaleIfNotExists($this->locale, 'Español', 'España');
        $packLanguageId = Uuid::randomHex();
        $this->languageId = $this->createLanguageWithPackLanguage($this->locale, $localeId, $packLanguageId);
        $this->createSnippetSetsForLocale($this->locale);
    }

    public function testSalesChannelSnippetSetChanged(): void
    {
        $languagePackSnippetSetId = $this->getLanguagePackSnippetSetId($this->locale);
        $domainId = $this->createSalesChannelDomainIfNotExists($this->languageId, $languagePackSnippetSetId, TestDefaults::SALES_CHANNEL);

        $criteria = new Criteria([$domainId]);
        $domain = $this->salesChannelDomainRepository->search($criteria, $this->context)->first();
        static::assertNotNull($domain);
        static::assertSame($languagePackSnippetSetId, $domain->getSnippetSetId());

        $cleanupReplacedLanguageService = new CleanupReplacedLanguage(
            $this->languageRepository,
            $this->snippetSetRepository,
            $this->packLanguageRepository,
            $this->salesChannelDomainRepository,
            $this->getContainer()->get(Connection::class)
        );

        $cleanupReplacedLanguageService->changeSalesChannelDomainSnippetSet($this->locale, $this->context);

        $baseSnippetSetId = $this->getBaseSnippetSetId($this->locale);

        $criteria = new Criteria([$domainId]);
        $domain = $this->salesChannelDomainRepository->search($criteria, $this->context)->first();
        static::assertNotNull($domain);
        static::assertSame($baseSnippetSetId, $domain->getSnippetSetId());
    }

    public function testLanguageRelationIsRemoved(): void
    {
        $criteria = new Criteria([$this->languageId]);
        $criteria->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

        $language = $this->languageRepository->search($criteria, $this->context)->first();
        static::assertNotNull($language);
        static::assertNotNull($language->get(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME));

        $cleanupReplacedLanguageService = new CleanupReplacedLanguage(
            $this->languageRepository,
            $this->snippetSetRepository,
            $this->packLanguageRepository,
            $this->salesChannelDomainRepository,
            $this->getContainer()->get(Connection::class)
        );
        $cleanupReplacedLanguageService->removeLanguageRelation($this->locale, $this->context);

        $criteria = new Criteria([$this->languageId]);
        $criteria->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

        $language = $this->languageRepository->search($criteria, $this->context)->first();
        static::assertInstanceOf(LanguageEntity::class, $language);
        static::assertFalse($language->has(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME));
    }

    public function testLanguagePackSnippetSetIsDeleted(): void
    {
        $this->createSnippetSetsForLocale($this->locale);
        $languagePackSnippetSetId = $this->getLanguagePackSnippetSetId($this->locale);
        $baseSnippetSetId = $this->getBaseSnippetSetId($this->locale);

        $cleanupReplacedLanguageService = new CleanupReplacedLanguage(
            $this->languageRepository,
            $this->snippetSetRepository,
            $this->packLanguageRepository,
            $this->salesChannelDomainRepository,
            $this->getContainer()->get(Connection::class)
        );

        $cleanupReplacedLanguageService->removeLanguagePackSnippetSet($this->locale, $this->context);

        $criteria = new Criteria([$languagePackSnippetSetId]);
        $languagePackSnippetSet = $this->snippetSetRepository->search($criteria, $this->context)->first();
        static::assertNull($languagePackSnippetSet);

        $criteria = new Criteria([$baseSnippetSetId]);
        $baseSnippetSet = $this->snippetSetRepository->search($criteria, $this->context)->first();
        static::assertNotNull($baseSnippetSet);
    }

    private function createLocaleIfNotExists(string $code, string $name, string $territory): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', $code));
        $localeId = $this->localeRepository->searchIds($criteria, $this->context)->firstId();

        if ($localeId !== null) {
            return $localeId;
        }

        $localeId = Uuid::randomHex();
        $this->localeRepository->create([
            [
                'id' => $localeId,
                'code' => $code,
                'name' => $name,
                'territory' => $territory,
            ],
        ], $this->context);

        return $localeId;
    }

    private function createLanguageWithPackLanguage(string $locale, string $localeId, string $packLanguageId): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('localeId', $localeId));
        $criteria->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

        $existingLanguage = $this->languageRepository->search($criteria, $this->context)->first();

        if ($existingLanguage !== null) {
            $languageId = $existingLanguage->getId();

            $packLanguage = $existingLanguage->get(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

            if ($packLanguage === null) {
                $this->packLanguageRepository->create([
                    [
                        'id' => $packLanguageId,
                        'languageId' => $languageId,
                        'administrationActive' => true,
                        'salesChannelActive' => true,
                    ],
                ], $this->context);
            }

            return $languageId;
        }

        $languageId = Uuid::randomHex();

        $this->languageRepository->create([
            [
                'id' => $languageId,
                'name' => "Test Language $locale",
                'localeId' => $localeId,
                'translationCodeId' => $localeId,
                'active' => true,
            ],
        ], $this->context);

        $this->packLanguageRepository->create([
            [
                'id' => $packLanguageId,
                'languageId' => $languageId,
                'administrationActive' => true,
                'salesChannelActive' => true,
            ],
        ], $this->context);

        return $languageId;
    }

    private function createSnippetSetsForLocale(string $locale): void
    {
        $languagePackSnippetSetName = "LanguagePack $locale";
        $baseSnippetSetName = "BASE $locale";

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $languagePackSnippetSetName));
        $languagePackSnippetSetId = $this->snippetSetRepository->searchIds($criteria, $this->context)->firstId();

        if ($languagePackSnippetSetId === null) {
            $this->snippetSetRepository->create([
                [
                    'name' => $languagePackSnippetSetName,
                    'baseFile' => "messages.$locale",
                    'iso' => $locale,
                ],
            ], $this->context);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $baseSnippetSetName));
        $baseSnippetSetId = $this->snippetSetRepository->searchIds($criteria, $this->context)->firstId();

        if ($baseSnippetSetId === null) {
            $this->snippetSetRepository->create([
                [
                    'name' => $baseSnippetSetName,
                    'baseFile' => "messages.$locale",
                    'iso' => $locale,
                ],
            ], $this->context);
        }
    }

    private function createSalesChannelDomainIfNotExists(
        string $languageId,
        string $snippetSetId,
        string $salesChannelId
    ): string {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        $criteria->addFilter(new EqualsFilter('languageId', $languageId));
        $criteria->addFilter(new EqualsFilter('snippetSetId', $snippetSetId));

        $existingDomainId = $this->salesChannelDomainRepository->searchIds($criteria, $this->context)->firstId();

        if ($existingDomainId !== null) {
            return $existingDomainId;
        }

        $domainId = Uuid::randomHex();
        $this->salesChannelDomainRepository->create([
            [
                'id' => $domainId,
                'salesChannelId' => $salesChannelId,
                'languageId' => $languageId,
                'snippetSetId' => $snippetSetId,
                'currencyId' => Defaults::CURRENCY,
                'url' => "http://localhost:8000/{$this->locale}",
            ],
        ], $this->context);

        return $domainId;
    }

    private function getBaseSnippetSetId(string $locale): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', "BASE $locale"));
        $baseSnippetSetId = $this->snippetSetRepository->searchIds($criteria, $this->context)->firstId();
        static::assertNotNull($baseSnippetSetId);

        return $baseSnippetSetId;
    }

    private function getLanguagePackSnippetSetId(string $locale): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', "LanguagePack $locale"));
        $languagePackSnippetSetId = $this->snippetSetRepository->searchIds($criteria, $this->context)->firstId();
        static::assertNotNull($languagePackSnippetSetId);

        return $languagePackSnippetSetId;
    }
}
