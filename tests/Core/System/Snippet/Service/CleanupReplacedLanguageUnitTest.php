<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetEntity;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use Shopware\Core\Test\Stub\Framework\IdsCollection;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\PackLanguage\PackLanguageCollection;

/**
 * @internal
 */
#[CoversClass(CleanupReplacedLanguage::class)]
class CleanupReplacedLanguageUnitTest extends TestCase
{
    private Connection&MockObject $connection;

    private IdsCollection $ids;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->ids = new IdsCollection();
    }

    public function testChangeSalesChannelDomainSnippetSetReturnsEarlyForAllCases(): void
    {
        $locale = 'es-ES';
        $context = Context::createDefaultContext();

        /** @var StaticEntityRepository<SnippetSetCollection> $snippetSetRepository */
        $snippetSetRepository = new StaticEntityRepository([
            new SnippetSetCollection([]),
            new SnippetSetCollection([]),
        ]);

        /** @var StaticEntityRepository<SalesChannelDomainCollection> $salesChannelDomainRepository */
        $salesChannelDomainRepository = new StaticEntityRepository([
            fn () => static::fail('The SalesChannelDomainRepository should not be searched (first early return should have been hit)'),
        ]);
        $service = $this->createCleanupReplacedLanguage(null, $snippetSetRepository, null, $salesChannelDomainRepository);

        $service->changeSalesChannelDomainSnippetSet($locale, $context);

        $languagePackSnippetSetId = Uuid::randomHex();
        $baseSnippetSetId = Uuid::randomHex();
        $languagePackSnippetSet = $this->createLanguagePackSnippetSet($languagePackSnippetSetId, $locale);
        $baseSnippetSet = $this->createBaseSnippetSet($baseSnippetSetId, $locale);

        /** @var StaticEntityRepository<SnippetSetCollection> $snippetSetRepository */
        $snippetSetRepository = new StaticEntityRepository([
            new SnippetSetCollection([$languagePackSnippetSet]),
            new SnippetSetCollection([$baseSnippetSet]),
        ]);

        /** @var StaticEntityRepository<SalesChannelDomainCollection> $salesChannelDomainRepository */
        $salesChannelDomainRepository = new StaticEntityRepository([
            new IdSearchResult(0, [], new Criteria(), $context),
        ]);

        $service = $this->createCleanupReplacedLanguage(null, $snippetSetRepository, null, $salesChannelDomainRepository);

        $service->changeSalesChannelDomainSnippetSet($locale, $context);

        static::assertCount(0, $salesChannelDomainRepository->updates);
    }

    public function testRemoveLanguageRelationReturnsEarlyForAllCases(): void
    {
        $locale = 'es-ES';
        $context = Context::createDefaultContext();
        $languageId = $this->ids->get('language');

        $this->connection->expects(static::never())->method('executeStatement');

        /** @var StaticEntityRepository<SnippetSetCollection> $snippetSetRepository */
        $snippetSetRepository = new StaticEntityRepository([
            new SnippetSetCollection([]),
            new SnippetSetCollection([$this->createLanguagePackSnippetSet(Uuid::randomHex(), $locale)]),
            new SnippetSetCollection([$this->createLanguagePackSnippetSet(Uuid::randomHex(), $locale)]),
        ]);

        /** @var StaticEntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = new StaticEntityRepository([
            fn () => static::fail('The LanguageRepository should not be searched (first early return should have been hit)'),
        ]);

        $emptySnippetSetService = $this->createCleanupReplacedLanguage($languageRepository, $snippetSetRepository, null, null);

        $emptySnippetSetService->removeLanguageRelation($locale, $context);

        /** @var StaticEntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = new StaticEntityRepository([
            new SnippetSetCollection([]),
        ]);

        /** @var StaticEntityRepository<PackLanguageCollection> $packLanguageRepository */
        $packLanguageRepository = new StaticEntityRepository([
            fn () => static::fail('The PackLanguageRepository should not be searched (second early return should have been hit)'),
        ]);

        $invalidLanguageResultService = $this->createCleanupReplacedLanguage($languageRepository, $snippetSetRepository, $packLanguageRepository, null);

        $invalidLanguageResultService->removeLanguageRelation($locale, $context);

        /** @var StaticEntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = new StaticEntityRepository([
            new LanguageCollection([$this->createLanguage($languageId)]),
        ]);

        /** @var StaticEntityRepository<PackLanguageCollection> $packLanguageRepository */
        $packLanguageRepository = new StaticEntityRepository([new PackLanguageCollection()]);

        $languageWithoutPackRelationService = $this->createCleanupReplacedLanguage($languageRepository, $snippetSetRepository, $packLanguageRepository, null);

        // Last call. Will succeed if the connection won't execute a statement
        $languageWithoutPackRelationService->removeLanguageRelation($locale, $context);
    }

    public function testRemoveLanguagePackSnippetSetReturnsEarlyForAllCases(): void
    {
        $locale = 'es-ES';
        $context = Context::createDefaultContext();

        /** @var StaticEntityRepository<SnippetSetCollection> $snippetSetRepository */
        $snippetSetRepository = new StaticEntityRepository([new SnippetSetCollection([])]);

        $emptySnippetSetService = $this->createCleanupReplacedLanguage(null, $snippetSetRepository, null, null);

        $emptySnippetSetService->removeLanguagePackSnippetSet($locale, $context);
        static::assertCount(0, $snippetSetRepository->deletes);
    }

    private function createLanguagePackSnippetSet(string $languagePackSnippetSetId, string $locale): SnippetSetEntity
    {
        $languagePackSnippetSet = new SnippetSetEntity();
        $languagePackSnippetSet->setId($languagePackSnippetSetId);
        $languagePackSnippetSet->setName("LanguagePack $locale");
        $languagePackSnippetSet->setIso($locale);

        return $languagePackSnippetSet;
    }

    private function createBaseSnippetSet(string $baseSnippetSetId, string $locale): SnippetSetEntity
    {
        $baseSnippetSet = new SnippetSetEntity();
        $baseSnippetSet->setId($baseSnippetSetId);
        $baseSnippetSet->setName("BASE $locale");
        $baseSnippetSet->setIso($locale);

        return $baseSnippetSet;
    }

    private function createLanguage(string $languageId): LanguageEntity
    {
        $language = new LanguageEntity();
        $language->setId($languageId);

        return $language;
    }

    /**
     * @param StaticEntityRepository<LanguageCollection>|null $languageRepository
     * @param StaticEntityRepository<SnippetSetCollection>|null $snippetSetRepository
     * @param StaticEntityRepository<PackLanguageCollection>|null $packLanguageRepository
     * @param StaticEntityRepository<SalesChannelDomainCollection>|null $salesChannelDomainRepository
     */
    private function createCleanupReplacedLanguage(
        ?StaticEntityRepository $languageRepository,
        ?StaticEntityRepository $snippetSetRepository,
        ?StaticEntityRepository $packLanguageRepository,
        ?StaticEntityRepository $salesChannelDomainRepository,
    ): CleanupReplacedLanguage {
        /** @var StaticEntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = $languageRepository ?? new StaticEntityRepository([]);

        /** @var StaticEntityRepository<SnippetSetCollection> $snippetSetRepository */
        $snippetSetRepository = $snippetSetRepository ?? new StaticEntityRepository([]);

        /** @var StaticEntityRepository<PackLanguageCollection> $packLanguageRepository */
        $packLanguageRepository = $packLanguageRepository ?? new StaticEntityRepository([]);

        /** @var StaticEntityRepository<SalesChannelDomainCollection> $salesChannelDomainRepository */
        $salesChannelDomainRepository = $salesChannelDomainRepository ?? new StaticEntityRepository([]);

        return new CleanupReplacedLanguage(
            languageRepository: $languageRepository,
            snippetSetRepository: $snippetSetRepository,
            packLanguageRepository: $packLanguageRepository,
            salesChannelDomainRepository: $salesChannelDomainRepository,
            connection: $this->connection
        );
    }
}
