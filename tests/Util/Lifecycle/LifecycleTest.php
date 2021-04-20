<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Test\Cart\Promotion\Helpers\Fakes\FakeResultStatement;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Migration\MigrationCollection;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Swag\LanguagePack\Extension\LanguageExtension;
use Swag\LanguagePack\PackLanguage\PackLanguageEntity;
use Swag\LanguagePack\SwagLanguagePack;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;

class LifecycleTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testDeactivateWithNoLinkToASalesChannel(): void
    {
        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        $lifecycle = new Lifecycle($this->getConnectionMock(), $languageRepository);
        $lifecycle->deactivate($this->getDeactivateContext());

        static::assertTrue(true);
    }

    public function testDeactivateWithLinkedSalesChannels(): void
    {
        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');
        $lifecycle = new Lifecycle($this->getConnectionMock(), $languageRepository);

        $deactivateContext = $this->getDeactivateContext();
        $this->addPackLanguageLanguageToSalesChannel($languageRepository, $deactivateContext->getContext());

        $this->expectExceptionMessage('The following languages provided by SwagLanguagePack are still used by SalesChannels: Nederlands');
        $lifecycle->deactivate($deactivateContext);
    }

    public function testUninstallWithoutKeepingUserData(): void
    {
        $connection = $this->getConnectionMock();

        $connection->expects(static::atLeast(4))
            ->method('executeStatement')
            ->willReturnOnConsecutiveCalls([false, true]);

        $connection->expects(static::atLeast(2))
            ->method('executeQuery')
            ->willReturn(new FakeResultStatement([]));

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        $lifecycle = new Lifecycle($connection, $languageRepository);
        $lifecycle->uninstall($this->getUninstallContext());
    }

    public function testUninstallKeepsUserData(): void
    {
        $connection = $this->getConnectionMock();
        $uninstallContext = $this->getUninstallContext(true);

        $connection->expects(static::never())
            ->method('executeStatement');

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        $lifecycle = new Lifecycle($connection, $languageRepository);
        $lifecycle->uninstall($uninstallContext);
    }

    private function getDeactivateContext(): DeactivateContext
    {
        return new DeactivateContext(
            new SwagLanguagePack(true, ''),
            Context::createDefaultContext(),
            '',
            '',
            $this->createMock(MigrationCollection::class)
        );
    }

    private function getUninstallContext(bool $keepUserData = false): UninstallContext
    {
        return new UninstallContext(
            new SwagLanguagePack(true, ''),
            Context::createDefaultContext(),
            '',
            '',
            $this->createMock(MigrationCollection::class),
            $keepUserData
        );
    }

    /**
     * @return Connection|MockObject
     */
    private function getConnectionMock()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeStatement', 'executeQuery'])
            ->getMock();
    }

    private function addPackLanguageLanguageToSalesChannel(EntityRepositoryInterface $languageRepository, Context $context): void
    {
        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $this->getContainer()->get('sales_channel.repository');

        /** @var EntityRepositoryInterface $packLanguageRepository */
        $packLanguageRepository = $this->getContainer()->get('swag_language_pack_language.repository');

        $languageCriteria = (new Criteria())
            ->addAssociation(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME)
            ->addFilter(new EqualsFilter('name', 'Nederlands'))
            ->setLimit(1);

        /** @var LanguageEntity $dutchLanguage */
        $dutchLanguage = $languageRepository->search($languageCriteria, $context)->first();

        /** @var PackLanguageEntity|null $packLanguage */
        $packLanguage = $dutchLanguage->get(LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME);
        static::assertInstanceOf(PackLanguageEntity::class, $packLanguage);

        $packLanguageData = [[
            'id' => $packLanguage->getId(),
            'salesChannelActive' => true,
        ]];
        $packLanguageRepository->update($packLanguageData, $context);

        $salesChannelCriteria = (new Criteria())
            ->addAssociation('languages')
            ->setLimit(1);

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search($salesChannelCriteria, $context)->first();

        $data = [[
            'id' => $salesChannel->getId(),
            'languages' => [[
                'id' => $dutchLanguage->getId(),
            ]],
        ]];
        $salesChannelRepository->update($data, $context);
    }
}
