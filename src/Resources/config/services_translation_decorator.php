<?php declare(strict_types=1);

namespace Swag\LanguagePack\Resources\config;

use Swag\LanguagePack\Core\System\Snippet\Service\CleanupReplacedLanguage;
use Swag\LanguagePack\Core\System\Snippet\Service\CleanupTranslationLoader;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return [
    CleanupTranslationLoader::class => [
        'decorates' => 'Shopware\Core\System\Snippet\Service\TranslationLoader',
        'arguments' => [
            service('.inner'),
            service(CleanupReplacedLanguage::class),
        ],
    ],
];
