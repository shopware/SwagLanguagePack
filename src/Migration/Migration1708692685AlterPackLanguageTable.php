<?php

declare(strict_types=1);

namespace Swag\LanguagePack\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

class Migration1708692685AlterPackLanguageTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1708692685;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            ALTER TABLE `#table#`
                DROP FOREIGN KEY `fk.swag_language_pack_language_language`;

            ALTER TABLE `#table#`
                ADD CONSTRAINT `fk.swag_language_pack_language_language`
                    FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE;
        SQL;

        $connection->executeStatement(\str_replace(
            ['#table#'],
            [PackLanguageDefinition::ENTITY_NAME],
            $sql,
        ));
    }

    public function updateDestructive(Connection $connection): void {}
}
