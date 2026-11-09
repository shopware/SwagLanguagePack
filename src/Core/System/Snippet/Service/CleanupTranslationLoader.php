<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\Snippet\Service;

use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\System\Snippet\Service\AbstractTranslationLoader;

/**
 * @internal
 *
 * Note: AbstractTranslationLoader only exists in Shopware 6.7.7+
 * This class is conditionally loaded via build() method in SwagLanguagePack.
 */
class CleanupTranslationLoader extends AbstractTranslationLoader
{
    public function __construct(
        private readonly AbstractTranslationLoader $decorated,
        private readonly CleanupReplacedLanguage $cleanupReplacedLanguage,
        private readonly LanguagePackSnippetSetMigrator $snippetSetMigrator,
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    public function getDecorated(): AbstractTranslationLoader
    {
        return $this->decorated;
    }

    public function load(string $locale, Context $context, bool $activate = true): void
    {
        // Before core creates `BASE <locale>`, so it adopts the renamed `LanguagePack <locale>` set instead.
        $this->migrateSnippetSet($locale);

        $this->decorated->load($locale, $context, $activate);

        $this->cleanupReplacedLanguage->removeLanguageRelation($locale, $context);
    }

    public function link(string $locale, Context $context, bool $activate = true): void
    {
        $this->migrateSnippetSet($locale);

        $this->decorated->link($locale, $context, $activate);

        $this->cleanupReplacedLanguage->removeLanguageRelation($locale, $context);
    }

    public function pluginTranslationExists(Plugin $plugin): bool
    {
        return $this->decorated->pluginTranslationExists($plugin);
    }

    public function getLocalesBasePath(): string
    {
        return $this->decorated->getLocalesBasePath();
    }

    public function getLocalePath(string $locale): string
    {
        return $this->decorated->getLocalePath($locale);
    }

    private function migrateSnippetSet(string $locale): void
    {
        $changedSetIds = $this->snippetSetMigrator->migrate($locale);

        if ($changedSetIds === []) {
            return;
        }

        $this->cacheInvalidator->invalidate(array_map(Translator::tag(...), $changedSetIds));
    }
}
