<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Storefront\Framework\Routing\CachedDomainLoader;
use Swag\LanguagePack\Core\System\Snippet\Service\SnippetSetCacheInvalidator;

/**
 * @internal
 */
#[CoversClass(SnippetSetCacheInvalidator::class)]
class SnippetSetCacheInvalidatorTest extends TestCase
{
    public function testInvalidatesNothingWithoutChangedSnippetSets(): void
    {
        $cacheInvalidator = $this->createMock(CacheInvalidator::class);
        $cacheInvalidator->expects(static::never())->method('invalidate');

        (new SnippetSetCacheInvalidator($cacheInvalidator))->invalidate([]);
    }

    public function testInvalidatesTranslatorAndDomainCaches(): void
    {
        $calls = [];
        $cacheInvalidator = $this->createMock(CacheInvalidator::class);
        $cacheInvalidator->expects(static::exactly(2))
            ->method('invalidate')
            ->willReturnCallback(static function (array $tags, bool $force = false) use (&$calls): void {
                $calls[] = [$tags, $force];
            });

        (new SnippetSetCacheInvalidator($cacheInvalidator))->invalidate(['first-set-id', 'second-set-id']);

        static::assertSame(
            [
                [[Translator::tag('first-set-id'), Translator::tag('second-set-id')], false],
                [['routing-domains', CachedDomainLoader::DOMAIN_COLLECTION_CACHE_KEY], true],
            ],
            $calls,
        );
    }
}
