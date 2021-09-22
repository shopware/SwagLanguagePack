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
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelLanguage\SalesChannelLanguageDefinition;
use Swag\LanguagePack\Test\Helper\ServicesTrait;

class SalesChannelLanguageValidatorTest extends TestCase
{
    use ServicesTrait;

    private EntityRepositoryInterface $salesChannelLanguageRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepositoryInterface $salesChannelLanguageRepository */
        $salesChannelLanguageRepository = $container->get(\sprintf('%s.repository', SalesChannelLanguageDefinition::ENTITY_NAME));
        $this->salesChannelLanguageRepository = $salesChannelLanguageRepository;
    }

    public function testCreatingASalesChannelLanguageWithADeactivatedLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $languageId = $this->setSalesChannelActiveForLanguageByName('Dansk', false, $context);

        $this->expectExceptionMessage(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $languageId));
        $this->createSalesChannelLanguage($languageId, $context);
    }

    private function createSalesChannelLanguage(string $languageId, Context $context): void
    {
        $this->salesChannelLanguageRepository->create([
            [
                'salesChannelId' => Defaults::SALES_CHANNEL,
                'languageId' => $languageId,
            ],
        ], $context);
    }
}
