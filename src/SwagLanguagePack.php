<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\Language\LanguageCollection;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;
use Swag\LanguagePack\Util\Migration\MigrationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SwagLanguagePack extends Plugin
{
    public const SUPPORTED_LANGUAGES = [
        'Bahasa Indonesia' => 'id-ID',
        'Bosanski' => 'bs-BA',
        'български език' => 'bg-BG',
        'Čeština' => 'cs-CZ',
        'Dansk' => 'da-DK',
        'Ελληνικά' => 'el-GR',
        'English (US)' => 'en-US',
        'Español' => 'es-ES',
        'Suomi' => 'fi-FI',
        'Français' => 'fr-FR',
        'हिन्दी' => 'hi-IN',
        'Hrvatski' => 'hr-HR',
        'Magyar' => 'hu-HU',
        'Italiano' => 'it-IT',
        '한국어' => 'ko-KR',
        'Latviešu' => 'lv-LV',
        'Nederlands' => 'nl-NL',
        'Norsk' => 'nn-NO',
        'Polski' => 'pl-PL',
        'Português' => 'pt-PT',
        'Română' => 'ro-RO',
        'Русский' => 'ru-RU',
        'Slovenčina' => 'sk-SK',
        'Slovenščina' => 'sl-SI',
        'Srpski' => 'sr-RS',
        'Svenska' => 'sv-SE',
        'Türkçe' => 'tr-TR',
        'Українська' => 'uk-UA',
        'Tiếng Việt Nam' => 'vi-VN',
    ];

    public const BASE_SNIPPET_SET_LOCALES = [
        'bs-BA',
        'bg-BG',
        'cs-CZ',
        'da-DK',
        'el-GR',
        'en-US',
        'es-ES',
        'fi-FI',
        'fr-FR',
        'hi-IN',
        'hr-HR',
        'hu-HU',
        'id-ID',
        'it-IT',
        'ko-KR',
        'lv-LV',
        'nl-NL',
        'nn-NO',
        'pl-PL',
        'pt-PT',
        'ro-RO',
        'ru-RU',
        'sk-SK',
        'sl-SI',
        'sr-RS',
        'sv-SE',
        'tr-TR',
        'uk-UA',
        'vi-VN',
    ];

    /**
     * @return array<string, array<string>>
     */
    public function enrichPrivileges(): array
    {
        return [
            AclRoleDefinition::ALL_ROLE_KEY => [
                'swag_language_pack_language:read',
                'language:read',
            ],
            'language.editor' => [
                'swag_language_pack_language:read',
                'swag_language_pack_language:update',
            ],
        ];
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        /** @var EntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        (new Lifecycle($connection, $languageRepository))->uninstall($uninstallContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $migrationHelper = new MigrationHelper($connection);

        $migrationHelper->createPackLanguages();
        $migrationHelper->createSnippetSets();
    }
}
