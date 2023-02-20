<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\System\SalesChannel\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\CommandTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Maintenance\SalesChannel\Service\SalesChannelCreator;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Swag\LanguagePack\Core\System\SalesChannel\Command\SalesChannelCreateCommand;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Swag\LanguagePack\SwagLanguagePack;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SalesChannelCreateCommandTest extends TestCase
{
    use IntegrationTestBehaviour;
    use CommandTestBehaviour;

    protected SalesChannelCreateCommand $salesChannelCreateCommand;

    protected SalesChannelCreateCommand $overrideSalesChannelCreateCommand;

    protected EntityRepository $salesChannelRepository;

    protected EntityRepository $languageRepository;

    protected EntityRepository $languagePackRepository;

    private Context $context;

    protected function setUp(): void
    {
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->getContainer()->get(\sprintf('%s.repository', SalesChannelDefinition::ENTITY_NAME));
        $this->salesChannelRepository = $salesChannelRepository;

        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->getContainer()->get(\sprintf('%s.repository', LanguageDefinition::ENTITY_NAME));
        $this->languageRepository = $languageRepository;

        /** @var EntityRepository $languagePackRepository */
        $languagePackRepository = $this->getContainer()->get(\sprintf('%s.repository', PackLanguageDefinition::ENTITY_NAME));
        $this->languagePackRepository = $languagePackRepository;

        $this->context = Context::createDefaultContext();

        /** @var DefinitionInstanceRegistry $definitionRegistry */
        $definitionRegistry = $this->getContainer()->get(DefinitionInstanceRegistry::class);

        /** @var SalesChannelCreator $salesChannelCreator */
        $salesChannelCreator = $this->getContainer()->get(SalesChannelCreator::class);

        $this->salesChannelCreateCommand = new SalesChannelCreateCommand(
            $definitionRegistry,
            $languageRepository,
            $salesChannelCreator
        );
    }

    public function testIfCommandSucceeds(): void
    {
        $salesChannelId = 'ad1028c2a8ed46d2a24f189812b1a23c';
        $navigationCategoryId = $this->getValidCategoryId();

        $input = new StringInput("--id=$salesChannelId --navigationCategoryId=$navigationCategoryId");
        $output = new BufferedOutput();

        $this->runCommand($this->salesChannelCreateCommand, $input, $output);

        $outputString = $output->fetch();

        static::assertStringNotContainsString('[ERROR]', $outputString);

        // check the associated languages of the created sales channel
        $associatedLanguages = $this->getAssociatedLanguageLocalesOfSalesChannel($salesChannelId);

        static::assertStringContainsString('[OK] Sales channel has been created successfully.', $outputString);
        static::assertContains('en-GB', $associatedLanguages);
        static::assertContains('de-DE', $associatedLanguages);
    }

    public function testIfCommandConsidersActivatedLanguages(): void
    {
        $salesChannelId = 'ad1028c2a8ed46d2a24f189812b1a23b';

        $navigationCategoryId = $this->getValidCategoryId();

        $input = new StringInput("--id=$salesChannelId --navigationCategoryId=$navigationCategoryId");

        $output = new BufferedOutput();

        $this->runCommand($this->salesChannelCreateCommand, $input, $output);

        $outputString = $output->fetch();

        static::assertStringContainsString('[OK] Sales channel has been created successfully.', $outputString);
        static::assertStringNotContainsString('[ERROR]', $outputString);

        // check the associated languages of the created sales channel
        $associatedLanguages = $this->getAssociatedLanguageLocalesOfSalesChannel($salesChannelId);
        static::assertCount(0, array_diff(SwagLanguagePack::BASE_SNIPPET_SET_LOCALES, $associatedLanguages));
    }

    /**
     * @return string[]
     */
    protected function getAssociatedLanguageLocalesOfSalesChannel(string $salesChannelId): array
    {
        // fetch the language with the locale
        $criteria = new Criteria([$salesChannelId]);
        $criteria->addAssociation('languages.locale');

        /** @var SalesChannelEntity|null $result */
        $result = $this->salesChannelRepository->search($criteria, $this->context)->first();
        static::assertNotNull($result);
        $languages = $result->getLanguages();
        static::assertNotNull($languages);

        return \array_map(static function (LanguageEntity $lang) {
            $locale = $lang->getLocale();
            static::assertNotNull($locale);

            return $locale->getCode();
        }, \array_values($languages->getElements()));
    }
}
