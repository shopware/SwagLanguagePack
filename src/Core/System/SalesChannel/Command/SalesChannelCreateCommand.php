<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\SalesChannel\Command;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
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
 * in a meaningful way (the `getAllIdsOf(...)` function was private). Also the version requirement of the core
 * could not be raised to make that function protected.
 */
class SalesChannelCreateCommand extends Command
{
    protected static $defaultName = 'sales-channel:create';

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $shippingMethodRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $snippetSetRepository;

    /**
     * @var DefinitionInstanceRegistry
     */
    private $definitionRegistry;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

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
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->countryRepository = $countryRepository;
        $this->snippetSetRepository = $snippetSetRepository;
        $this->categoryRepository = $categoryRepository;
        $this->languageRepository = $languageRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Id for the sales channel', Uuid::randomHex())
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name for the application')
            ->addOption('languageId', null, InputOption::VALUE_REQUIRED, 'Default language', Defaults::LANGUAGE_SYSTEM)
            ->addOption('snippetSetId', null, InputOption::VALUE_REQUIRED, 'Default snippet set')
            ->addOption('currencyId', null, InputOption::VALUE_REQUIRED, 'Default currency', Defaults::CURRENCY)
            ->addOption('paymentMethodId', null, InputOption::VALUE_REQUIRED, 'Default payment method')
            ->addOption('shippingMethodId', null, InputOption::VALUE_REQUIRED, 'Default shipping method')
            ->addOption('countryId', null, InputOption::VALUE_REQUIRED, 'Default country')
            ->addOption('typeId', null, InputOption::VALUE_OPTIONAL, 'Sales channel type id')
            ->addOption('customerGroupId', null, InputOption::VALUE_REQUIRED, 'Default customer group', Defaults::FALLBACK_CUSTOMER_GROUP)
            ->addOption('navigationCategoryId', null, InputOption::VALUE_REQUIRED, 'Default Navigation Category')
        ;
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
            'customerGroupId' => $input->getOption('customerGroupId'),
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

    protected function getSalesChannelConfiguration(InputInterface $input, OutputInterface $output): array
    {
        return [
            'navigationCategoryId' => $this->getRootCategoryId(),
        ];
    }

    protected function getTypeId(): string
    {
        return Defaults::SALES_CHANNEL_TYPE_API;
    }

    protected function getFirstActiveShippingMethodId(): ?string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true));

        return $this->shippingMethodRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }

    protected function getFirstActivePaymentMethodId(): ?string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position'));

        return $this->paymentMethodRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }

    protected function getFirstActiveCountryId(): ?string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position'));

        return $this->countryRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }

    protected function getSnippetSetId(): string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('iso', 'en-GB'));

        $id = $this->snippetSetRepository->searchIds($criteria, Context::createDefaultContext())->firstId();

        if ($id === null) {
            throw new \InvalidArgumentException('Unable to get default SnippetSet. Please provide a valid SnippetSetId.');
        }

        return $id;
    }

    protected function getAllIdsOf(string $entity, Context $context): array
    {
        $repository = $this->definitionRegistry->getRepository($entity);

        /** @var IdSearchResult $ids */
        $ids = $context->disableCache(function (Context $context) use ($repository): IdSearchResult {
            return $repository->searchIds(new Criteria(), $context);
        });

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

    protected function getRootCategoryId(): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('category.parentId', null));
        $criteria->addSorting(new FieldSorting('category.createdAt', FieldSorting::ASCENDING));

        return $this->categoryRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }

    protected function getAllActiveLanguageIds(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', true),
            new EqualsFilter('swagLanguagePackLanguage.salesChannelActive', null),
        ]));

        /** @var IdSearchResult $ids */
        $ids = $context->disableCache(function (Context $context) use ($criteria): IdSearchResult {
            return $this->languageRepository->searchIds($criteria, $context);
        });

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
}
