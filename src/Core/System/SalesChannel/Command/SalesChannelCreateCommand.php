<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\SalesChannel\Command;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\Maintenance\SalesChannel\Service\SalesChannelCreator;
use Shopware\Core\System\Language\LanguageCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class is a modified version of Shopware\Core\System\SalesChannel\Command\SalesChannelCreateCommand
 *
 * It only changes how the language ids are collected to exclude deactivated languages of the language pack
 * for the sales channel creation.
 *
 * The reason it completely overrides the core command is that it wasn't possible to decorate or extend it
 * in a meaningful way (the `getAllIdsOf(...)` function was private). Also, the version requirement of the core
 * could not be raised to make that function protected.
 */
#[AsCommand(
    name: 'sales-channel:create',
    description: 'Creates a new sales channel',
)]
class SalesChannelCreateCommand extends Command
{
    /**
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionRegistry,
        private readonly EntityRepository $languageRepository,
        private readonly SalesChannelCreator $salesChannelCreator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id for the sales channel', Uuid::randomHex())
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name for the application')
            ->addOption('typeId', null, InputOption::VALUE_OPTIONAL, 'Sales channel type id')
            ->addOption('languageId', null, InputOption::VALUE_OPTIONAL, 'Default language', Defaults::LANGUAGE_SYSTEM)
            ->addOption('currencyId', null, InputOption::VALUE_OPTIONAL, 'Default currency', Defaults::CURRENCY)
            ->addOption('paymentMethodId', null, InputOption::VALUE_OPTIONAL, 'Default payment method')
            ->addOption('shippingMethodId', null, InputOption::VALUE_OPTIONAL, 'Default shipping method')
            ->addOption('countryId', null, InputOption::VALUE_OPTIONAL, 'Default country')
            ->addOption('customerGroupId', null, InputOption::VALUE_OPTIONAL, 'Default customer group')
            ->addOption('navigationCategoryId', null, InputOption::VALUE_OPTIONAL, 'Default Navigation Category');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $context = Context::createDefaultContext();

        try {
            /** @var list<string> $languages */
            $languages = $this->getAllActiveLanguageIds($context);

            /** @var list<string> $paymentMethods */
            $paymentMethods = $this->getAllIdsOf('payment_method', $context);

            /** @var list<string> $countries */
            $countries = $this->getAllIdsOf('country', $context);

            $accessKey = $this->salesChannelCreator->createSalesChannel(
                $input->getOption('id'),
                $input->getOption('name') ?? 'Headless',
                $input->getOption('typeId') ?? $this->getTypeId(),
                $input->getOption('languageId'),
                $input->getOption('currencyId'),
                $input->getOption('paymentMethodId'),
                $input->getOption('shippingMethodId'),
                $input->getOption('countryId'),
                $input->getOption('customerGroupId'),
                $input->getOption('navigationCategoryId'),
                null,
                $languages,
                $input->getOption('shippingMethodId') ? [$input->getOption('shippingMethodId')] : null,
                $paymentMethods,
                $countries,
                $this->getSalesChannelConfiguration($input, $output),
            );

            $io->success('Sales channel has been created successfully.');
        } catch (WriteException $exception) {
            $io->error('Something went wrong.');

            $messages = [];
            foreach ($exception->getExceptions() as $err) {
                if ($err instanceof WriteConstraintViolationException) {
                    foreach ($err->getViolations() as $violation) {
                        $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                    }
                }
            }

            $io->listing($messages);

            return self::SUCCESS;
        }

        $io->text('Access tokens:');

        $table = new Table($output);
        $table->setHeaders(['Key', 'Value']);

        $table->addRows([
            ['Access key', $accessKey],
        ]);

        $table->render();

        return self::SUCCESS;
    }

    protected function getTypeId(): string
    {
        return Defaults::SALES_CHANNEL_TYPE_API;
    }

    /**
     * @return array<null>
     */
    protected function getSalesChannelConfiguration(InputInterface $input, OutputInterface $output): array
    {
        return [];
    }

    /**
     * @return list<string>|list<array<string, string>>
     */
    protected function getAllIdsOf(string $entity, Context $context): array
    {
        $repository = $this->definitionRegistry->getRepository($entity);
        $ids = $repository->searchIds(new Criteria(), $context);

        return $ids->getIds();
    }

    /**
     * @return list<string>|list<array<string, string>>
     */
    protected function getAllActiveLanguageIds(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', true),
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', null),
        ]));

        return $this->languageRepository->searchIds($criteria, $context)->getIds();
    }
}
