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
        $previousDefault = $this->getCurrentSystemLanguage();
        $newDefault = $this->getLanguageByLocale($locale);

        $this->getDecorated()->setDefaultLanguage($locale);

        if ($previousDefault['id'] === $newDefault['id']) {
            return;
        }

        $this->swapLanguagePackLanguageReferences($newDefault, $previousDefault);
    }

    private function swapLanguagePackLanguageReferences(array $newDefault, array $previousDefault): void
    {
        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($newDefault, $previousDefault): void {
            $statement = $connection->prepare('
                UPDATE language
                SET swag_language_pack_language_id = :referenceId
                WHERE id = :id
            ');

            $statement->executeStatement([
                'id' => $previousDefault['id'],
                'referenceId' => $newDefault['swag_language_pack_language_id'],
            ]);

            $statement->executeStatement([
                'id' => $newDefault['id'],
                'referenceId' => $previousDefault['swag_language_pack_language_id'],
            ]);
        });
    }

    /**
     * @return array<string, string>|null
     */
    private function getCurrentSystemLanguage(): ?array
    {
        return $this->connection->fetchAssociative(
            'SELECT id, swag_language_pack_language_id
             FROM language
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
            'SELECT language.id, language.swag_language_pack_language_id
             FROM `language`
             INNER JOIN locale ON locale.id = language.translation_code_id
             WHERE LOWER(locale.code) = LOWER(:locale)',
            ['locale' => $locale]
        ) ?: null;
    }

}
