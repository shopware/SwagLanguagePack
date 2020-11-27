<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swag\LanguagePack\Util\Lifecycle\Uninstaller;

class SwagLanguagePack extends Plugin
{
    public const SUPPORTED_LANGUAGES = [
        'Bosanski' => 'bs-BA',
        'Čeština' => 'cs-CZ',
        'Dansk' => 'da-DK',
        'Español' => 'es-ES',
        'Français' => 'fr-FR',
        'Bahasa Indonesia' => 'id-ID',
        'Italiano' => 'it-IT',
        'Latviešu' => 'lv-LV',
        'Nederlands' => 'nl-NL',
        'Polski' => 'pl-PL',
        'Português' => 'pt-PT',
        'Русский' => 'ru-RU',
        'Svenska' => 'sv-SE',
    ];

    public const BASE_SNIPPET_SET_LOCALES = [
        'bs-BA',
        'cs-CZ',
        'da-DK',
        'es-ES',
        'fr-FR',
        'id-ID',
        'it-IT',
        'lv-LV',
        'nl-NL',
        'pl-PL',
        'pt-PT',
        'ru-RU',
        'sv-SE',
    ];

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new Uninstaller($connection))->uninstall($uninstallContext);
    }
}
