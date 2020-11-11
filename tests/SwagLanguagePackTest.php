<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginDefinition;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\LanguagePack\SwagLanguagePack;

class SwagLanguagePackTest extends TestCase
{
    use KernelTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $pluginRepository;

    protected function setUp(): void
    {
        /** @var EntityRepositoryInterface $pluginRepository */
        $pluginRepository = $this->getContainer()->get(\sprintf('%s.repository', PluginDefinition::ENTITY_NAME));
        $this->pluginRepository = $pluginRepository;
    }

    public function testThatPluginIsInstalled(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('baseClass', SwagLanguagePack::class)
        );

        /** @var PluginEntity|null $plugin */
        $plugin = $this->pluginRepository->search($criteria, Context::createDefaultContext())->first();

        static::assertNotNull($plugin, 'Plugin needs to be installed to run testsuite');
    }
}
