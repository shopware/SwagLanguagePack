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
        // ToDo LAN-23 - Fill with all languages
        'Čeština' => 'cs-CZ',
        'Dansk' => 'da-DK',
        'Deutsch' => 'de-DE',
    ];

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new Uninstaller($connection))->uninstall($uninstallContext);
    }
}
