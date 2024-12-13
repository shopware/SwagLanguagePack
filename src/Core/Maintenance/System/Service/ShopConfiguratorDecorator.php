<?php

namespace Swag\LanguagePack\Core\Maintenance\System\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Maintenance\System\Service\ShopConfigurator;

/**
 * @internal
 */
class ShopConfiguratorDecorator extends ShopConfigurator
{
    public function __construct(
        private readonly ShopConfigurator $decorated,
        private readonly Connection $connection
    )
    {
        parent::__construct($connection);
    }

    public function getDecorated(): ShopConfigurator
    {
        return $this->decorated;
    }

    /**
     * @param string $locale
     * @return void
     */
    public function setDefaultLanguage(string $locale): void
    {
        $newDefault = $this->getLanguageByLocale($locale);
        $currentDefault = $this->getCurrentSystemLanguage();

        $this->getDecorated()->setDefaultLanguage($locale);

        if ($currentDefault['language_pack_id'] ?? null === $newDefault['language_pack_id'] ?? null) {
            return;
        }

        $this->swapLanguagePackLanguageReferences($currentDefault, $newDefault);
    }

    /**
     * @param array{language_id: string, language_pack_id: string}|null $currentDefault
     * @param array{language_id: string, language_pack_id: string}|null $newDefault
     * @return void
     */
    private function swapLanguagePackLanguageReferences(?array $currentDefault, ?array $newDefault): void
    {
        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($currentDefault, $newDefault): void {
            $statement = $connection->prepare('
                UPDATE swag_language_pack_language
                SET language_id = CASE id
                    WHEN :idA THEN :languageIdB
                    WHEN :idB THEN :languageIdA
                    ELSE language_id
                    END
                WHERE id IN (:idA, :idB)
            ');

            $statement->executeStatement([
                'languageIdA' => $currentDefault['language_id'],
                'languageIdB' => $newDefault['language_id'],
                'idA' => $currentDefault['language_pack_id'],
                'idB' => $newDefault['language_pack_id'],
            ]);
        });

        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($currentDefault, $newDefault): void {
            $statement = $connection->prepare('
                UPDATE language
                SET swag_language_pack_language_id = CASE id
                    WHEN :idA THEN :languagePackIdB
                    WHEN :idB THEN :languagePackIdA
                    ELSE swag_language_pack_language_id
                    END
                WHERE id IN (:idA, :idB)
            ');

            $statement->executeStatement([
                'idA' => $currentDefault['language_id'],
                'idB' => $newDefault['language_id'],
                'languagePackIdA' => $currentDefault['language_pack_id'],
                'languagePackIdB' => $newDefault['language_pack_id'],
            ]);
        });
    }

    /**
     * @return array{language_id: string, language_pack_id: string}|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getCurrentSystemLanguage(): ?array
    {
        return $this->connection->fetchAssociative(
            'SELECT language.id as language_id, swag_language_pack_language.id as language_pack_id
             FROM language
             RIGHT JOIN swag_language_pack_language ON language.id = swag_language_pack_language.language_id
             WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM)]
        ) ?: null;
    }

    /**
     * @return array{language_id: string, language_pack_id: string}|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getLanguageByLocale(string $locale): ?array
    {
        return $this->connection->fetchAssociative(
            'SELECT language.id as language_id, swag_language_pack_language.id as language_pack_id
             FROM language
             INNER JOIN locale ON locale.id = language.translation_code_id
             RIGHT JOIN swag_language_pack_language ON language.id = swag_language_pack_language.language_id
             WHERE LOWER(locale.code) = LOWER(:locale)',
            ['locale' => $locale]
        ) ?: null;
    }
}
