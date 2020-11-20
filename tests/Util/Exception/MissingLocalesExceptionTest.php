<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Util\Exception;

use PHPUnit\Framework\TestCase;
use Swag\LanguagePack\Util\Exception\MissingLocalesException;

class MissingLocalesExceptionTest extends TestCase
{
    public function testGetStatusCode(): void
    {
        $exception = new MissingLocalesException(['doesnt-EXIST', 'thatis-NOLOCALE']);

        static::assertSame(
            'No LocaleEntities associated to the following locale codes: doesnt-EXIST, thatis-NOLOCALE',
            $exception->getMessage()
        );
        static::assertSame('SWAG_LANGUAGE_PACK__PACK_LANGUAGES_WITHOUT_LOCALES', $exception->getErrorCode());
    }
}
