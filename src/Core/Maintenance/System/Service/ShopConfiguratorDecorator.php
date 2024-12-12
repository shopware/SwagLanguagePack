<?php

namespace Swag\LanguagePack\Core\Maintenance\System\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Maintenance\System\Service\ShopConfigurator;

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

    public function setDefaultLanguage(string $locale): void
    {
        $newDefault = $this->getLanguageByLocale($locale);
        $currentDefault = $this->getCurrentSystemLanguage();

        $this->getDecorated()->setDefaultLanguage($locale);

        if ($currentDefault['language_pack_id'] === $newDefault['language_pack_id']) {
            return;
        }

        $this->swapLanguagePackLanguageReferences($currentDefault, $newDefault);
    }

    private function swapLanguagePackLanguageReferences(array $currentDefault, array $newDefault): void
    {
        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($currentDefault, $newDefault): void {
            $statement = $connection->prepare('
                UPDATE swag_language_pack_language
                SET language_id = :languageId
                WHERE id = :id
            ');

            $statement->executeStatement([
                'languageId' => $currentDefault['language_id'],
                'id' => $newDefault['language_pack_id'],
            ]);

            $statement->executeStatement([
                'languageId' => $newDefault['language_id'],
                'id' => $currentDefault['language_pack_id'],
            ]);
        });
    }

    /**
     * @return array<string, string>|null
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
     * @return array<string, string>|null
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
