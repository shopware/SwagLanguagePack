<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\Extension\LanguageExtension;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

class LanguageExtensionTest extends TestCase
{
    public function testExtendFieldsAddsOneToOneAssociationField(): void
    {
        /** @var MockObject|FieldCollection $collection */
        $collection = $this->getMockBuilder(FieldCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();

        $collection
            ->expects(static::atLeastOnce())
            ->method('add')
            ->with(
                (new OneToOneAssociationField(
                    LanguageExtension::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME,
                    'id',
                    'language_id',
                    PackLanguageDefinition::class,
                    false
                ))->addFlags(new SetNullOnDelete())
            );

        (new LanguageExtension())->extendFields($collection);
    }

    public function testGetDefinitionClassReturnsCmsSectionDefinitionClass(): void
    {
        static::assertSame(
            LanguageDefinition::class,
            (new LanguageExtension())->getDefinitionClass()
        );
    }
}
