<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\Framework\DataAbstractionLayer\Write\Validation;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelLanguage\SalesChannelLanguageDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\Test\TestDefaults;
use Swag\LanguagePack\Test\Helper\ServicesTrait;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class SalesChannelLanguageValidatorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var EntityRepository<SalesChannelCollection>
     */
    private EntityRepository $salesChannelLanguageRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepository<SalesChannelCollection> $salesChannelLanguageRepository */
        $salesChannelLanguageRepository = $container->get(\sprintf('%s.repository', SalesChannelLanguageDefinition::ENTITY_NAME));
        $this->salesChannelLanguageRepository = $salesChannelLanguageRepository;
    }

    public function testCreatingASalesChannelLanguageWithADeactivatedLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $languageId = $this->prepareSalesChannelActiveForLanguageByName('Dansk', false, $context);

        $this->expectExceptionObject(new WriteConstraintViolationException(
            new ConstraintViolationList([
                new ConstraintViolation(\sprintf('The language with the id "%s" is disabled for all Sales Channels.', $languageId), null, [], '', null, null),
            ]),
        ));

        try {
            $this->createSalesChannelLanguage($languageId, $context);
        } catch (WriteException $e) {
            foreach ($e->getExceptions() as $inner) {
                throw $inner;
            }
            throw $e;
        }
    }

    private function createSalesChannelLanguage(string $languageId, Context $context): void
    {
        $this->salesChannelLanguageRepository->create([[
            'salesChannelId' => TestDefaults::SALES_CHANNEL,
            'languageId' => $languageId,
        ]], $context);
    }
}
