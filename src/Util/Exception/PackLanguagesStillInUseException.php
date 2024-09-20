<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Shopware\Core\System\Language\LanguageCollection;

/**
 * @deprecated tag:v5.0.0 - Will be removed. Use LanguagePackException::packLanguagesStillInUse() instead
 */
class PackLanguagesStillInUseException extends ShopwareHttpException
{
    public const ERROR_CODE = LanguagePackException::PACK_LANGUAGES_STILL_IN_USE;

    public function __construct(LanguageCollection $languages)
    {
        $languagePackException = LanguagePackException::packLanguagesStillInUse($languages);

        parent::__construct(
            $languagePackException->getMessage(),
            $languagePackException->getParameters(),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
