<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Swag\LanguagePack\Util\Migration\MigrationHelper;

class Migration1605167857AddPackLanguage extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1605167857;
    }

    public function update(Connection $connection): void
    {
        $migrationHelper = new MigrationHelper($connection);
        $migrationHelper->createPackLanguageTable();
        $migrationHelper->alterLanguageAddPackLanguageColumn();
        $migrationHelper->createPackLanguages();
        $migrationHelper->createSnippetSets();
    }

    public function updateDestructive(Connection $connection): void {}
}
