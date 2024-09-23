<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Exception;

use PHPUnit\Framework\TestCase;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Swag\LanguagePack\Util\Exception\LanguagePackException;

class LanguagePackExceptionTest extends TestCase
{
    public function testInstallWithoutLocalesException(): void
    {
        $exception = LanguagePackException::installWithoutLocales(['doesnt-EXIST', 'thatis-NOLOCALE']);

        static::assertSame(
            'No LocaleEntities associated to the following locale codes: doesnt-EXIST, thatis-NOLOCALE',
            $exception->getMessage(),
        );
        static::assertSame('SWAG_LANGUAGE_PACK__PACK_LANGUAGES_WITHOUT_LOCALES', $exception->getErrorCode());
    }

    public function testPackLanguagesStillInUseException(): void
    {
        $language = new LanguageEntity();
        $language->setUniqueIdentifier('NobodyDoesCareAboutThisAtAll');
        $language->setName('CoolDutch');

        $exception = LanguagePackException::packLanguagesStillInUse(new LanguageCollection([$language]));

        static::assertSame(
            "The following languages provided by Shopware's LanguagePack are still used by Sales Channels: CoolDutch",
            $exception->getMessage(),
        );
        static::assertSame('SWAG_LANGUAGE_PACK_LANGUAGE__STILL_IN_USE_IN_SALES_CHANNEL', $exception->getErrorCode());
    }
}
