<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;

class Uninstaller
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->dropConstraints();
        $this->dropTables();
        $this->dropColumns();
    }

    private function dropConstraints(): void
    {
        $constraintName = 'fk.language_swag_language_pack_language';
        $replaceVariables = ['#table#', '#constraint#'];
        $dropData = [
            [LanguageDefinition::ENTITY_NAME, $constraintName],
        ];

        foreach ($dropData as $dropColumn) {
            $checkSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'SELECT * 
                FROM `information_schema`.`REFERENTIAL_CONSTRAINTS` 
                WHERE `CONSTRAINT_NAME` = "#constraint#"
                AND `TABLE_NAME` = "#table#"
                AND `CONSTRAINT_SCHEMA` = DATABASE();'
            );

            $constraintExists = (bool) $this->connection->executeQuery($checkSql)->fetch();

            if (!$constraintExists) {
                continue;
            }

            $dropSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'ALTER TABLE `#table#`
                    DROP FOREIGN KEY `#constraint#`;'
            );

            $this->connection->executeUpdate($dropSql);
        }
    }

    private function dropTables(): void
    {
        $classNames = [
            PackLanguageDefinition::ENTITY_NAME,
        ];

        foreach ($classNames as $className) {
            $this->connection->executeUpdate(\sprintf('DROP TABLE IF EXISTS `%s`', $className));
        }
    }

    private function dropColumns(): void
    {
        $replaceVariables = ['#table#', '#column#'];
        $dropData = [
            [LanguageDefinition::ENTITY_NAME, PackLanguageDefinition::PACK_LANGUAGE_FOREIGN_KEY_STORAGE_NAME],
        ];

        foreach ($dropData as $dropColumn) {
            $checkSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'SHOW COLUMNS FROM `#table#`
                LIKE "#column#";'
            );

            $columnExists = (bool) $this->connection->executeQuery($checkSql)->fetch();

            if (!$columnExists) {
                continue;
            }

            $dropSql = \str_replace(
                $replaceVariables,
                $dropColumn,
                'ALTER TABLE `#table#`
                        DROP COLUMN `#column#`;'
            );

            $this->connection->executeUpdate($dropSql);
        }
    }
}
