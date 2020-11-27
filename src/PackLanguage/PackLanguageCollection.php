<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\PackLanguage;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(PackLanguageEntity $entity)
 * @method void                           set(string $key, PackLanguageEntity $entity)
 * @method \Generator<PackLanguageEntity> getIterator()
 * @method PackLanguageEntity[]           getElements()
 * @method PackLanguageEntity|null        get(string $key)
 * @method PackLanguageEntity|null        first()
 * @method PackLanguageEntity|null        last()
 */
class PackLanguageCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PackLanguageEntity::class;
    }
}
