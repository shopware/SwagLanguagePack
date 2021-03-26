<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Storefront\Framework\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Framework\Command\SalesChannelCreateStorefrontCommand as StorefrontSalesChannelCreateCommand;
use Shopware\Storefront\Storefront;
use Swag\LanguagePack\Storefront\Framework\Command\SalesChannelCreateStorefrontCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SalesChannelCreateStorefrontCommandTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languagePackLanguageRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Command
     */
    private $command;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function setUp(): void
    {
        if (!\class_exists(Storefront::class)) {
            static::markTestSkipped('Skip test: Storefront bundle is not installed');
        }

        $this->salesChannelRepository = $this->getContainer()->get('sales_channel.repository');
        $this->languagePackLanguageRepository = $this->getContainer()->get('swag_language_pack_language.repository');
        $this->languageRepository = $this->getContainer()->get('language.repository');
        $this->command = $this->getContainer()->get(StorefrontSalesChannelCreateCommand::class);

        $this->context = Context::createDefaultContext();
    }

    public function testServiceIsDecoratedCorrectly(): void
    {
        static::assertInstanceOf(SalesChannelCreateStorefrontCommand::class, $this->command);
    }

    public function testItCreatesStorefrontWithDefaultLanguagesIfAllLanguagesAreDisabled(): void
    {
        $this->setLanguagesActive(false);

        $storefrontId = Uuid::randomHex();

        $tester = new CommandTester($this->command);

        $tester->execute([
            '--id' => $storefrontId,
            '--url' => '"http://test.shop"',
        ]);

        $storefront = $this->salesChannelRepository->search($this->getStorefrontCriteria($storefrontId), $this->context)
            ->getEntities()->first();

        static::assertNotNull($storefront);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('swagLanguagePackLanguageId', null));

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

        $storefrontId = Uuid::randomHex();

        $tester = new CommandTester($this->command);

        $tester->execute([
            '--id' => $storefrontId,
            '--url' => '"http://test.shop"',
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
