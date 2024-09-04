<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\PackLanguage;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class PackLanguageDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_language_pack_language';
    public const PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME = self::ENTITY_NAME . '_id';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * @return array<string, bool>
     */
    public function getDefaults(): array
    {
        return [
            'administrationActive' => true,
        ];
    }

    public function getCollectionClass(): string
    {
        return PackLanguageCollection::class;
    }

    public function getEntityClass(): string
    {
        return PackLanguageEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new BoolField('administration_active', 'administrationActive'),
            new BoolField('sales_channel_active', 'salesChannelActive'),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))
                ->addFlags(new Required()),

            (new OneToOneAssociationField(
                LanguageDefinition::ENTITY_NAME,
                'language_id',
                'id',
                LanguageDefinition::class,
                true,
            ))->addFlags(new RestrictDelete()),
        ]);
    }
}
