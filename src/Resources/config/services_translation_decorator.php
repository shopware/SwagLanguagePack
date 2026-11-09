<?php declare(strict_types=1);

use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupTranslationLoader;
use Swag\LanguagePack\Core\System\Snippet\Service\LanguagePackSnippetSetMigrator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return [
    'services' => [
        CleanupTranslationLoader::class => [
            'decorates' => 'Shopware\Core\System\Snippet\Service\TranslationLoader',
            'arguments' => [
                service('.inner'),
                service(CleanupReplacedLanguage::class),
                service(LanguagePackSnippetSetMigrator::class),
                service(CacheInvalidator::class),
            ],
        ],
    ],
];
