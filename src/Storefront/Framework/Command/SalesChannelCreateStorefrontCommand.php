<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Storefront\Framework\Command;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Maintenance\SalesChannel\Service\SalesChannelCreator;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use Swag\LanguagePack\Core\System\SalesChannel\Command\SalesChannelCreateCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sales-channel:create:storefront',
    description: 'Creates a new storefront sales channel',
)]
class SalesChannelCreateStorefrontCommand extends SalesChannelCreateCommand
{
    /**
     * @param EntityRepository<LanguageCollection> $languageRepository
     * @param EntityRepository<SnippetSetCollection> $snippetSetRepository
     */
    public function __construct(
        protected readonly DefinitionInstanceRegistry $definitionRegistry,
        protected readonly EntityRepository $languageRepository,
        protected readonly SalesChannelCreator $salesChannelCreator,
        protected readonly EntityRepository $snippetSetRepository,
    ) {
        parent::__construct(
            $definitionRegistry,
            $languageRepository,
            $salesChannelCreator,
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'App URL for storefront')
            ->addOption('snippetSetId', null, InputOption::VALUE_OPTIONAL, 'Snippet set ID for translation');
    }

    protected function getTypeId(): string
    {
        return Defaults::SALES_CHANNEL_TYPE_STOREFRONT;
    }

    protected function getSnippetSetId(): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('iso', 'en-GB'));

        /** @var string|null $id */
        $id = $this->snippetSetRepository->searchIds($criteria, Context::createDefaultContext())->getIds()[0] ?? null;

        if ($id === null) {
            throw new \InvalidArgumentException('Unable to get default SnippetSet. Please provide a valid SnippetSetId.');
        }

        return $id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSalesChannelConfiguration(InputInterface $input, OutputInterface $output): array
    {
        return \array_filter([
            'domains' => [
                [
                    'url' => $input->getOption('url'),
                    'languageId' => $input->getOption('languageId'),
                    'snippetSetId' => $input->getOption('snippetSetId') ?? $this->getSnippetSetId(),
                    'currencyId' => $input->getOption('currencyId'),
                ],
            ],
            'navigationCategoryId' => $input->getOption('navigationCategoryId'),
            'name' => $input->getOption('name') ?? 'Storefront',
        ]);
    }
}
