<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\Content;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\LanguagePack\Core\Content\PackLanguageRepositoryDecorator;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

class PackLanguageRepositoryDecoratorTest extends TestCase
{
    use KernelTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $repositoryDecorator;

    protected function setUp(): void
    {
        /** @var EntityRepositoryInterface $repo */
        $repo = $this->getContainer()->get(\sprintf('%s.repository', PackLanguageDefinition::ENTITY_NAME));
        $this->repositoryDecorator = $repo;
    }

    public function testThatRepositoryGetsDecorated(): void
    {
        static::assertInstanceOf(PackLanguageRepositoryDecorator::class, $this->repositoryDecorator);
    }

    public function testThatLanguageLocaleAssociationIsAddedForSearchMethods(): void
    {
        $criteria = new Criteria();
        static::assertEmpty($criteria->getAssociations());

        $clonedSearchCriteria = $this->repositoryDecorator->search($criteria, Context::createDefaultContext())->getCriteria();
        $clonedSearchIdsCriteria = $this->repositoryDecorator->searchIds($criteria, Context::createDefaultContext())->getCriteria();

        static::assertCount(1, $clonedSearchCriteria->getAssociations());
        static::assertCount(1, $clonedSearchIdsCriteria->getAssociations());
    }
}
