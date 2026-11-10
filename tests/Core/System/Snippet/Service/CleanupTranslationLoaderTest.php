<?php declare(strict_types=1);

namespace Swag\LanguagePack\Test\Core\System\Snippet\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Snippet\Service\TranslationLoader;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupTranslationLoader;
use Swag\LanguagePack\Core\System\Snippet\Service\LanguagePackSnippetSetMigrator;
use Swag\LanguagePack\Core\System\Snippet\Service\SnippetSetCacheInvalidator;

/**
 * @internal
 */
#[CoversClass(CleanupTranslationLoader::class)]
class CleanupTranslationLoaderTest extends TestCase
{
    private TranslationLoader&MockObject $translationLoader;

    private CleanupReplacedLanguage&MockObject $cleanupReplacedLanguage;

    private LanguagePackSnippetSetMigrator&MockObject $snippetSetMigrator;

    private SnippetSetCacheInvalidator&MockObject $snippetSetCacheInvalidator;

    /**
     * @var list<string>
     */
    private array $calls = [];

    protected function setUp(): void
    {
        $this->translationLoader = $this->createMock(TranslationLoader::class);
        $this->cleanupReplacedLanguage = $this->createMock(CleanupReplacedLanguage::class);
        $this->snippetSetMigrator = $this->createMock(LanguagePackSnippetSetMigrator::class);
        $this->snippetSetCacheInvalidator = $this->createMock(SnippetSetCacheInvalidator::class);
    }

    public function testGetDecorated(): void
    {
        static::assertSame($this->translationLoader, $this->createCleanupTranslationLoader()->getDecorated());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function installMethodProvider(): iterable
    {
        yield 'load' => ['load'];
        yield 'link' => ['link'];
    }

    #[DataProvider('installMethodProvider')]
    public function testMigratesSnippetSetBeforeCoreCreatesBaseSet(string $method): void
    {
        $this->snippetSetMigrator
            ->expects(static::once())
            ->method('migrate')
            ->with('es-ES')
            ->willReturnCallback(function (): array {
                $this->calls[] = 'migrate';

                return [];
            });

        $this->translationLoader
            ->expects(static::once())
            ->method($method)
            ->willReturnCallback(function () use ($method): void {
                $this->calls[] = $method;
            });

        $this->cleanupReplacedLanguage
            ->expects(static::once())
            ->method('removeLanguageRelation')
            ->willReturnCallback(function (): void {
                $this->calls[] = 'removeLanguageRelation';
            });

        $this->snippetSetCacheInvalidator->expects(static::once())->method('invalidate')->with([]);

        $loader = $this->createCleanupTranslationLoader();
        $context = Context::createDefaultContext();

        if ($method === 'link') {
            $loader->link('es-ES', $context);
        } else {
            $loader->load('es-ES', $context);
        }

        static::assertSame(['migrate', $method, 'removeLanguageRelation'], $this->calls);
    }

    public function testInvalidatesCachesOfChangedSnippetSets(): void
    {
        $this->snippetSetMigrator
            ->method('migrate')
            ->willReturn(['first-set-id', 'second-set-id']);

        $this->snippetSetCacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with(['first-set-id', 'second-set-id']);

        $this->createCleanupTranslationLoader()->load('es-ES', Context::createDefaultContext());
    }

    private function createCleanupTranslationLoader(): CleanupTranslationLoader
    {
        return new CleanupTranslationLoader(
            $this->translationLoader,
            $this->cleanupReplacedLanguage,
            $this->snippetSetMigrator,
            $this->snippetSetCacheInvalidator,
        );
    }
}
