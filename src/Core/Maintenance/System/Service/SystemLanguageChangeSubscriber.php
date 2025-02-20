<?php declare(strict_types=1);

namespace Swag\LanguagePack\Core\Maintenance\System\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Maintenance\System\Service\SystemLanguageChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class SystemLanguageChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SystemLanguageChangeEvent::class => 'onSystemLanguageChanged',
        ];
    }

    public function onSystemLanguageChanged(SystemLanguageChangeEvent $event): void
    {
        $newDefaultId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $previousDefaultId = Uuid::fromHexToBytes($event->previousLanguageId);

        try {
            $mapping = $this->getLanguagePackIds($newDefaultId, $previousDefaultId);
            $this->swapLanguagePackLanguageReferences($mapping);
        } catch (\Exception) {
        }
    }

    /**
     * @param array<string, string|null> $mapping
     */
    private function swapLanguagePackLanguageReferences(array $mapping): void
    {
        $languageIdA = array_keys($mapping)[0];
        $languageIdB = array_keys($mapping)[1];
        $languagePackIdA = array_values($mapping)[0];
        $languagePackIdB = array_values($mapping)[1];

        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($languageIdA, $languageIdB, $languagePackIdA, $languagePackIdB): void {
            $statement = $connection->prepare(
                <<<'SQL'
                    UPDATE swag_language_pack_language
                    SET language_id = CASE id
                        WHEN :languagePackIdA THEN :languageIdB
                        WHEN :languagePackIdB THEN :languageIdA
                        ELSE language_id
                    END
                    WHERE id IN (:languagePackIdA, :languagePackIdB)
                SQL
            );

            $statement->bindValue('languageIdA', $languageIdA);
            $statement->bindValue('languageIdB', $languageIdB);
            $statement->bindValue('languagePackIdA', $languagePackIdA);
            $statement->bindValue('languagePackIdB', $languagePackIdB);

            $statement->executeStatement();
        });

        RetryableTransaction::retryable($this->connection, function (Connection $connection) use ($languageIdA, $languageIdB, $languagePackIdA, $languagePackIdB): void {
            $statement = $connection->prepare(
                <<<'SQL'
                    UPDATE language
                    SET swag_language_pack_language_id = CASE id
                        WHEN :languageIdA THEN :languagePackIdB
                        WHEN :languageIdB THEN :languagePackIdA
                        ELSE swag_language_pack_language_id
                    END
                    WHERE id IN (:languageIdA, :languageIdB)
                SQL
            );

            $statement->bindValue('languageIdA', $languageIdA);
            $statement->bindValue('languageIdB', $languageIdB);
            $statement->bindValue('languagePackIdA', $languagePackIdA);
            $statement->bindValue('languagePackIdB', $languagePackIdB);

            $statement->executeStatement();
        });
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return array<string, string|null>
     */
    private function getLanguagePackIds(string $newDefaultId, string $previousDefaultId): array
    {
        /** @var array<string, string|null> $mapping */
        $mapping = $this->connection->fetchAllKeyValue(
            <<<'SQL'
                SELECT language.id as language_id, swag_language_pack_language.id as language_pack_id
                FROM language
                LEFT JOIN swag_language_pack_language ON language.id = swag_language_pack_language.language_id
                WHERE language.id IN (:previousId, :newId)
            SQL,
            [
                'previousId' => $previousDefaultId,
                'newId' => $newDefaultId,
            ],
        );

        return $mapping;
    }
}
