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
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleCollection;
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
     * @var EntityRepository<LocaleCollection>
     */
    protected EntityRepository $localeRepository;

    /**
     * @var EntityRepository<PackLanguageCollection>
     */
    protected EntityRepository $packLanguageRepository;

    private Context $context;

    private string $languageId;

    private string $packLanguageId;

    private string $locale;

    protected function setUp(): void
    {
        $this->languageRepository = $this->getContainer()->get('language.repository');
        $this->localeRepository = $this->getContainer()->get('locale.repository');
        $this->packLanguageRepository = $this->getContainer()->get('swag_language_pack_language.repository');

        $this->context = Context::createDefaultContext();

        $this->locale = 'es-ES';
        $localeId = $this->createLocaleIfNotExists($this->locale, 'Español', 'España');
        $this->packLanguageId = Uuid::randomHex();
        $this->languageId = $this->createLanguageWithPackLanguage($this->locale, $localeId, $this->packLanguageId);
    }

    public function testLanguageRelationIsRemoved(): void
    {
        $criteria = new Criteria([$this->languageId]);
        $criteria->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

        $language = $this->languageRepository->search($criteria, $this->context)->getEntities()->first();
        static::assertNotNull($language);
        static::assertNotNull($language->get(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME));

        $cleanupReplacedLanguageService = new CleanupReplacedLanguage(
            $this->languageRepository,
            $this->packLanguageRepository,
            $this->getContainer()->get(Connection::class)
        );
        $cleanupReplacedLanguageService->removeLanguageRelation($this->locale, $this->context);

        $criteria = new Criteria([$this->languageId]);
        $criteria->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);

        $language = $this->languageRepository->search($criteria, $this->context)->getEntities()->first();
        static::assertInstanceOf(LanguageEntity::class, $language);
        static::assertFalse($language->has(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME));

        $packLanguage = $this->packLanguageRepository->search(new Criteria([$this->packLanguageId]), $this->context)->getEntities()->first();
        static::assertNull($packLanguage);
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

        $existingLanguage = $this->languageRepository->search($criteria, $this->context)->getEntities()->first();

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
}
