<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class MissingLocalesException extends ShopwareHttpException
{
    public function __construct(array $localeCodes)
    {
        parent::__construct(
            'No LocaleEntities associated to the following locale codes: {{ localeCodes }}',
            ['localeCodes' => \implode(', ', $localeCodes)]
        );
    }

    public function getErrorCode(): string
    {
        return 'SWAG_LANGUAGE_PACK__PACK_LANGUAGES_WITHOUT_LOCALES';
    }
}
