<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\Snippet\Service;

use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Adapter\Translation\Translator;

/**
 * @internal {@see LanguagePackSnippetSetMigrator} writes with plain SQL, so no DAL event clears these caches.
 */
readonly class SnippetSetCacheInvalidator
{
    /**
     * Not the `CachedDomainLoader` constants: `CACHE_KEY` is deprecated, and `DOMAIN_COLLECTION_CACHE_KEY` is missing before 6.7.13.0.
     */
    private const DOMAIN_CACHE_KEYS = ['routing-domains', 'routing-domain-collection'];

    public function __construct(
        private CacheInvalidator $cacheInvalidator,
    ) {
    }

    /**
     * @param list<string> $changedSetIds
     */
    public function invalidate(array $changedSetIds): void
    {
        if ($changedSetIds === []) {
            return;
        }

        $this->cacheInvalidator->invalidate(array_map(Translator::tag(...), $changedSetIds));
        $this->cacheInvalidator->invalidate(self::DOMAIN_CACHE_KEYS, true);
    }
}
