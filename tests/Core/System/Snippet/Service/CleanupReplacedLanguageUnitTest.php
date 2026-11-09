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
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
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

    public function testRemoveLanguageRelationReturnsEarlyForAllCases(): void
    {
        $locale = 'es-ES';
        $context = Context::createDefaultContext();
        $languageId = $this->ids->get('language');

        $this->connection->expects(static::never())->method('createQueryBuilder');

        $languageRepository = new StaticEntityRepository([
            new LanguageCollection([]),
        ]);

        $packLanguageRepository = new StaticEntityRepository([
            static fn () => static::fail('The PackLanguageRepository should not be searched (first early return should have been hit)'),
        ]);

        $this->createCleanupReplacedLanguage($languageRepository, $packLanguageRepository)
            ->removeLanguageRelation($locale, $context);

        $languageRepository = new StaticEntityRepository([
            new LanguageCollection([$this->createLanguage($languageId)]),
        ]);

        $packLanguageRepository = new StaticEntityRepository([new PackLanguageCollection()]);

        $this->createCleanupReplacedLanguage($languageRepository, $packLanguageRepository)
            ->removeLanguageRelation($locale, $context);
    }

    private function createLanguage(string $languageId): LanguageEntity
    {
        $language = new LanguageEntity();
        $language->setId($languageId);

        return $language;
    }

    /**
     * @param StaticEntityRepository<LanguageCollection> $languageRepository
     * @param StaticEntityRepository<PackLanguageCollection> $packLanguageRepository
     */
    private function createCleanupReplacedLanguage(
        StaticEntityRepository $languageRepository,
        StaticEntityRepository $packLanguageRepository,
    ): CleanupReplacedLanguage {
        return new CleanupReplacedLanguage(
            $languageRepository,
            $packLanguageRepository,
            $this->connection
        );
    }
}
