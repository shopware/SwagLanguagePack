<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

class LanguageExtension extends EntityExtension
{
    public const PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME = 'swagLanguagePackLanguage';

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new FkField(
                PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME,
                'swagLanguagePackLanguageId',
                PackLanguageDefinition::class
            )
        );

        $collection->add(
            (new OneToOneAssociationField(
                self::PACK_LANGUAGE_ASSOCIATION_PROPERTY_NAME,
                PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME,
                'id',
                PackLanguageDefinition::class,
                false
            ))->addFlags(new SetNullOnDelete())
        );
    }
}
