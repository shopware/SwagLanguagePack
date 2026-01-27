<?php declare(strict_types=1);

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Snippet\Service\TranslationLoader;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupTranslationLoader;

#[CoversClass(CleanupTranslationLoader::class)]
class CleanupTranslationLoaderTest extends TestCase
{
    private TranslationLoader&MockObject $translationLoader;

    private CleanupReplacedLanguage&MockObject $cleanupReplacedLanguages;

    protected function setUp(): void
    {
        $this->translationLoader = $this->createMock(TranslationLoader::class);
        $this->cleanupReplacedLanguages = $this->createMock(CleanupReplacedLanguage::class);
    }

    public function testGetDecorated(): void
    {
        static::assertSame($this->translationLoader, $this->createCleanupTranslationLoader()->getDecorated());
    }

    public function testLoadCallsCleanupMethods(): void
    {
        $this->translationLoader
            ->expects(static::once())
            ->method('load');

        $this->cleanupReplacedLanguages
            ->expects(static::once())
            ->method('changeSalesChannelDomainSnippetSet');
        $this->cleanupReplacedLanguages
            ->expects(static::once())
            ->method('removeLanguageRelation');
        $this->cleanupReplacedLanguages
            ->expects(static::once())
            ->method('removeLanguagePackSnippetSet');

        $this->createCleanupTranslationLoader()->load('es-ES', Context::createDefaultContext());
    }

    private function createCleanupTranslationLoader(): CleanupTranslationLoader
    {
        return new CleanupTranslationLoader(
            $this->translationLoader,
            $this->cleanupReplacedLanguages
        );
    }
}
