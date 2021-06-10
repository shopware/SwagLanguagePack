<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swag\LanguagePack\Util\Lifecycle\Lifecycle;

class SwagLanguagePack extends Plugin
{
    public const SUPPORTED_LANGUAGES = [
        'Bahasa Indonesia' => 'id-ID',
        'Bosanski' => 'bs-BA',
        'български език' => 'bg-BG',
        'Čeština' => 'cs-CZ',
        'Dansk' => 'da-DK',
        'Ελληνικά' => 'el-GR',
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
        'Română' => 'ro-MD',
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
        'ro-MD',
        'ru-RU',
        'sk-SK',
        'sl-SI',
        'sr-RS',
        'sv-SE',
        'tr-TR',
        'uk-UA',
        'vi-VN',
    ];

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

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        (new Lifecycle($connection, $languageRepository))->deactivate($deactivateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        (new Lifecycle($connection, $languageRepository))->uninstall($uninstallContext);
    }
}
