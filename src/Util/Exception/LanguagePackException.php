<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Symfony\Component\HttpFoundation\Response;

class LanguagePackException extends HttpException
{
    public const INSTALL_WITHOUT_LOCALES = 'SWAG_LANGUAGE_PACK__PACK_LANGUAGES_WITHOUT_LOCALES';

    public const PACK_LANGUAGES_STILL_IN_USE = 'SWAG_LANGUAGE_PACK_LANGUAGE__STILL_IN_USE_IN_SALES_CHANNEL';

    /**
     * @param string[] $localeCodes
     */
    public static function installWithoutLocales(array $localeCodes): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INSTALL_WITHOUT_LOCALES,
            sprintf('No LocaleEntities associated to the following locale codes: %s', \implode(', ', $localeCodes)),
        );
    }

    public static function packLanguagesStillInUse(LanguageCollection $languages): self
    {
        $names = \array_map(static function (LanguageEntity $language): string {
            return $language->getName();
        }, $languages->getElements());

        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::PACK_LANGUAGES_STILL_IN_USE,
            sprintf('The following languages provided by Shopware\'s LanguagePack are still used by Sales Channels: %s', \implode(', ', $names)),
        );
    }
}
