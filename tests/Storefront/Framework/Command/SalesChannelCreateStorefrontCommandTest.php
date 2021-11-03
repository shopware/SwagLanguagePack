<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Storefront\Framework\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Maintenance\SalesChannel\Service\SalesChannelCreator;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Storefront;
use Swag\LanguagePack\Extension\LanguageExtension;
use Swag\LanguagePack\Storefront\Framework\Command\SalesChannelCreateStorefrontCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SalesChannelCreateStorefrontCommandTest extends TestCase
{
    use IntegrationTestBehaviour;

    private EntityRepositoryInterface $salesChannelRepository;

    private EntityRepositoryInterface $languagePackLanguageRepository;

    private EntityRepositoryInterface $languageRepository;

    private Context $context;

    private Command $command;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setUp(): void
    {
        if (!\class_exists(Storefront::class)) {
            static::markTestSkipped('Skip test: Storefront bundle is not installed');
        }

        /** @var DefinitionInstanceRegistry $definitionRegistry */
        $definitionRegistry = $this->getContainer()->get(DefinitionInstanceRegistry::class);

        /** @var SalesChannelCreator $salesChannelCreator */
        $salesChannelCreator = $this->getContainer()->get(SalesChannelCreator::class);

        /** @var EntityRepositoryInterface $snippetSetRepository */
        $snippetSetRepository = $this->getContainer()->get('snippet_set.repository');

        $this->salesChannelRepository = $this->getContainer()->get('sales_channel.repository');

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');
        $this->languageRepository = $languageRepository;

        /** @var EntityRepositoryInterface $languagePackLanguageRepository */
        $languagePackLanguageRepository = $this->getContainer()->get('swag_language_pack_language.repository');
        $this->languagePackLanguageRepository = $languagePackLanguageRepository;

        $this->command = new SalesChannelCreateStorefrontCommand(
            $definitionRegistry,
            $this->languageRepository,
            $salesChannelCreator,
            $snippetSetRepository
        );

        $this->context = Context::createDefaultContext();
    }

    public function testServiceIsDecoratedCorrectly(): void
    {
        static::assertInstanceOf(SalesChannelCreateStorefrontCommand::class, $this->command);
    }

    public function testItCreatesStorefrontWithDefaultLanguagesIfAllLanguagesAreDisabled(): void
    {
        $this->setLanguagesActive(false);
        $navigationCategoryId = $this->getValidCategoryId();

        $storefrontId = 'f37e50e6e2c8461dab552bdbc50e4566';

        $tester = new CommandTester($this->command);

        $tester->execute([
            '--id' => $storefrontId,
            '--url' => '"http://test.shop"',
            '--navigationCategoryId' => $navigationCategoryId,
        ]);

        $storefront = $this->salesChannelRepository->search($this->getStorefrontCriteria($storefrontId), $this->context)
            ->getEntities()->first();

        static::assertNotNull($storefront);

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                \sprintf('%s.id', LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME),
                null
            )
        );

        $activeLanguageIds = $this->languageRepository->searchIds($criteria, $this->context)->getIds();

        /**
         * @var LanguageCollection $associatedLanguages
         */
        $associatedLanguages = $storefront->getLanguages();
        $associatedLanguageIds = \array_values($associatedLanguages->getIds());

        static::assertCount(2, $activeLanguageIds);

        static::assertEquals(
            \sort($activeLanguageIds),
            \sort($associatedLanguageIds)
        );
    }

    public function testItCreatesStorefrontWithEnabledLanguages(): void
    {
        $this->setLanguagesActive(true);
        $navigationCategoryId = $this->getValidCategoryId();

        $storefrontId = Uuid::randomHex();

        $tester = new CommandTester($this->command);

        $tester->execute([
            '--id' => $storefrontId,
            '--url' => '"http://test.shop"',
            '--navigationCategoryId' => $navigationCategoryId,
        ]);

        /** @var SalesChannelEntity $storefront */
        $storefront = $this->salesChannelRepository->search($this->getStorefrontCriteria($storefrontId), $this->context)
            ->getEntities()->first();

        $activeLanguageIds = $this->languageRepository->searchIds(new Criteria(), $this->context)->getIds();

        /** @var LanguageCollection $associatedLanguages */
        $associatedLanguages = $storefront->getLanguages();
        $associatedLanguageIds = \array_values($associatedLanguages->getIds());

        static::assertEquals(
            \sort($activeLanguageIds),
            \sort($associatedLanguageIds)
        );
    }

    public function setLanguagesActive(bool $active): void
    {
        $ids = $this->languagePackLanguageRepository->searchIds(new Criteria(), $this->context)->getIds();

        $updateCommands = \array_map(function (string $id) use ($active): array {
            return [
                'id' => $id,
                'salesChannelActive' => $active,
            ];
        }, $ids);

        $this->languagePackLanguageRepository->update($updateCommands, $this->context);
    }

    private function getStorefrontCriteria(string $id): Criteria
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('domains');
        $criteria->addAssociation('languages');

        return $criteria;
    }
}
