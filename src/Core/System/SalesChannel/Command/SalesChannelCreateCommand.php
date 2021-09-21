<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\SalesChannel\Command;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\System\SalesChannel\Command\SalesChannelCreateCommand as InheritedSalesChannelCreateCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class is a modified version of Shopware\Core\System\SalesChannel\Command\SalesChannelCreateCommand
 *
 * It only changes how the language ids are collected to exclude deactivated languages of the language pack
 * for the sales channel creation.
 *
 * The reason it completely overrides the core command is that it wasn't possible to decorate or extend it
 * in a meaningful way (the `getAllIdsOf(...)` function was private). Also the version requirement of the core
 * could not be raised to make that function protected.
 */
class SalesChannelCreateCommand extends InheritedSalesChannelCreateCommand
{
    private EntityRepositoryInterface $salesChannelRepository;

    private DefinitionInstanceRegistry $definitionRegistry;

    private EntityRepositoryInterface $languageRepository;

    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $paymentMethodRepository,
        EntityRepositoryInterface $shippingMethodRepository,
        EntityRepositoryInterface $countryRepository,
        EntityRepositoryInterface $snippetSetRepository,
        EntityRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $languageRepository
    ) {
        $this->definitionRegistry = $definitionRegistry;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->languageRepository = $languageRepository;

        parent::__construct(
            $definitionRegistry,
            $salesChannelRepository,
            $paymentMethodRepository,
            $shippingMethodRepository,
            $countryRepository,
            $snippetSetRepository,
            $categoryRepository
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getOption('id');
        $typeId = $input->getOption('typeId');

        $io = new ShopwareStyle($input, $output);

        $paymentMethod = $input->getOption('paymentMethodId') ?? $this->getFirstActivePaymentMethodId();
        $shippingMethod = $input->getOption('shippingMethodId') ?? $this->getFirstActiveShippingMethodId();
        $countryId = $input->getOption('countryId') ?? $this->getFirstActiveCountryId();
        $snippetSet = $input->getOption('snippetSetId') ?? $this->getSnippetSetId();
        $customerGroupId = $input->getOption('customerGroupId') ?? $this->getCustomerGroupId();
        $context = Context::createDefaultContext();

        $data = [
            'id' => $id,
            'name' => $input->getOption('name') ?? 'Headless',
            'typeId' => $typeId ?? $this->getTypeId(),
            'accessKey' => AccessKeyHelper::generateAccessKey('sales-channel'),

            // default selection
            'languageId' => $input->getOption('languageId'),
            'snippetSetId' => $snippetSet,
            'currencyId' => $input->getOption('currencyId'),
            'currencyVersionId' => Defaults::LIVE_VERSION,
            'paymentMethodId' => $paymentMethod,
            'paymentMethodVersionId' => Defaults::LIVE_VERSION,
            'shippingMethodId' => $shippingMethod,
            'shippingMethodVersionId' => Defaults::LIVE_VERSION,
            'countryId' => $countryId,
            'countryVersionId' => Defaults::LIVE_VERSION,
            'customerGroupId' => $customerGroupId,
            'navigationCategoryId' => $input->getOption('navigationCategoryId'),

            // available mappings
            'currencies' => $this->getAllIdsOf('currency', $context),
            'languages' => $this->getAllActiveLanguageIds($context),
            'shippingMethods' => $this->getAllIdsOf('shipping_method', $context),
            'paymentMethods' => $this->getAllIdsOf('payment_method', $context),
            'countries' => $this->getAllIdsOf('country', $context),
        ];

        $data = \array_replace_recursive($data, $this->getSalesChannelConfiguration($input, $output));

        try {
            $this->salesChannelRepository->create([$data], Context::createDefaultContext());

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

            return 0;
        }

        $io->text('Access tokens:');

        $table = new Table($output);
        $table->setHeaders(['Key', 'Value']);

        $table->addRows([
            ['Access key', $data['accessKey']],
        ]);

        $table->render();

        return 0;
    }

    protected function getAllIdsOf(string $entity, Context $context): array
    {
        $repository = $this->definitionRegistry->getRepository($entity);
        $ids = $repository->searchIds(new Criteria(), $context);

        return \array_map(
            /**
             * @psalm-suppress MissingClosureParamType
             */
            function ($id) {
                return ['id' => $id];
            },
            $ids->getIds()
        );
    }

    protected function getAllActiveLanguageIds(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', true),
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', null),
        ]));

        $ids = $this->languageRepository->searchIds($criteria, $context);

        return \array_map(
            /**
             * @psalm-suppress MissingClosureParamType
             */
            function ($id) {
                return ['id' => $id];
            },
            $ids->getIds()
        );
    }

    private function getCustomerGroupId(): string
    {
        $criteria = (new Criteria())
            ->setLimit(1);

        $repository = $this->definitionRegistry->getRepository(CustomerGroupDefinition::ENTITY_NAME);

        $id = $repository->searchIds($criteria, Context::createDefaultContext())->firstId();

        if ($id === null) {
            throw new \RuntimeException('Cannot find a customer group to assign it to the sales channel');
        }

        return $id;
    }
}
