<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

/**
 * @deprecated tag:v5.0.0 - Will be removed. Use LanguagePackException::installingPackLanguagesWithoutLocales() instead
 */
class MissingLocalesException extends ShopwareHttpException
{
    /**
     * @param list<string>|array<int|string, string> $localeCodes
     */
    public function __construct(array $localeCodes)
    {
        $languagePackException = LanguagePackException::installWithoutLocales($localeCodes);

        parent::__construct(
            $languagePackException->getMessage(),
            $languagePackException->getParameters(),
        );
    }

    public function getErrorCode(): string
    {
        return LanguagePackException::INSTALL_WITHOUT_LOCALES;
    }
}
