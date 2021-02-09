<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Exception;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\ShopwareHttpException;
use Shopware\Core\System\Language\LanguageEntity;

class PackLanguagesStillInUseException extends ShopwareHttpException
{
    public const ERROR_CODE = 'SWAG_LANGUAGE_PACK_LANGUAGE__STILL_IN_USE_IN_SALES_CHANNEL';

    public function __construct(EntityCollection $languages)
    {
        $names = \array_map(static function (LanguageEntity $language) {
            return $language->getName();
        }, $languages->getElements());

        parent::__construct(
            'The following languages provided by SwagLanguagePack are still used by SalesChannels: {{ names }}',
            [
                'names' => \implode(', ', $names),
            ]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
