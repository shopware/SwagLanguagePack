<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Exception;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\System\Language\LanguageEntity;
use Swag\LanguagePack\Util\Exception\PackLanguagesStillInUseException;

class PackLanguageStillInUseExceptionTest extends TestCase
{
    public function testGetStatusCode(): void
    {
        $language = new LanguageEntity();
        $language->setUniqueIdentifier('NobodyDoesCareAboutThisAtAll');
        $language->setName('Nederlands');

        $exception = new PackLanguagesStillInUseException(new EntityCollection([$language]));

        static::assertSame(
            'The following languages provided by SwagLanguagePack are still used by SalesChannels: Nederlands',
            $exception->getMessage()
        );
        static::assertSame('SWAG_LANGUAGE_PACK_LANGUAGE__STILL_IN_USE_IN_SALES_CHANNEL', $exception->getErrorCode());
    }
}
