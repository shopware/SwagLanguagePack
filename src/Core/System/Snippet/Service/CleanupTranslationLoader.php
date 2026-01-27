<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\System\Snippet\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\System\Snippet\Service\AbstractTranslationLoader;

/**
 * @internal
 */
class CleanupTranslationLoader extends AbstractTranslationLoader
{
    public function __construct(
        private readonly AbstractTranslationLoader $decorated,
        private readonly CleanupReplacedLanguage $cleanupReplacedLanguage
    ) {
    }

    public function getDecorated(): AbstractTranslationLoader
    {
        return $this->decorated;
    }

    public function load(string $locale, Context $context, bool $activate = true): void
    {
        $this->decorated->load($locale, $context, $activate);

        $this->cleanupReplacedLanguage->changeSalesChannelDomainSnippetSet($locale, $context);
        $this->cleanupReplacedLanguage->removeLanguageRelation($locale, $context);
        $this->cleanupReplacedLanguage->removeLanguagePackSnippetSet($locale, $context);
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
}
