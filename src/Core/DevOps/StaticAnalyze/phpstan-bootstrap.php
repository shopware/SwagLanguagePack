<?php declare(strict_types=1);

namespace Swag\LanguagePack\PHPStan;

require_once __DIR__ . '/../../../../../../../src/Core/DevOps/StaticAnalyze/phpstan-bootstrap.php';

if (!class_exists(\Shopware\Core\System\Snippet\Service\AbstractTranslationLoader::class)) {
    require_once __DIR__ . '/../../../../stubs/Core/System/Snippet/Service/AbstractTranslationLoader.stub';
}
