<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\Framework\DataAbstractionLayer\Write\Validation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 */
abstract class AbstractLanguageValidator implements EventSubscriberInterface
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostWriteValidationEvent::class => 'postValidate',
        ];
    }

    /**
     * @throws WriteConstraintViolationException
     */
    public function postValidate(PostWriteValidationEvent $event): void
    {
        $violationList = new ConstraintViolationList();
        foreach ($event->getCommands() as $command) {
            if (!($command instanceof InsertCommand || $command instanceof UpdateCommand)
                || $command->getEntityName() !== $this->getSupportedEntity()
            ) {
                continue;
            }

            $this->validate($command, $violationList);
        }

        if ($violationList->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violationList));
        }
    }

    abstract protected function getSupportedEntity(): string;

    protected function validate(WriteCommand $command, ConstraintViolationList $violationList): void
    {
        $payload = $command->getPayload();
        if (!isset($payload['language_id'])) {
            return;
        }

        $languageId = $payload['language_id'];
        if (!$this->isLanguageManagedByLanguagePack($languageId) || $this->isSalesChannelLanguageAvailable($languageId)) {
            return;
        }

        $violationList->add(
            new ConstraintViolation(
                \sprintf('The language with the id "%s" is disabled for all Sales Channels.', Uuid::fromBytesToHex($languageId)),
                'The language with the id "{{ languageId }}" is disabled for all Sales Channels.',
                [$languageId],
                null,
                $command->getPath(),
                $languageId,
            ),
        );
    }

    protected function isSalesChannelLanguageAvailable(string $languageId): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('sales_channel_active')
            ->from(PackLanguageDefinition::ENTITY_NAME)
            ->where('language_id = :languageId')
            ->setParameter('languageId', $languageId)
            ->setMaxResults(1)
            ->executeQuery();

        return (bool) $statement->fetchOne();
    }

    protected function isLanguageManagedByLanguagePack(string $languageId): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('swag_language_pack_language_id')
            ->from(LanguageDefinition::ENTITY_NAME)
            ->where('id = :languageId')
            ->setParameter('languageId', $languageId)
            ->setMaxResults(1)
            ->executeQuery();

        return (bool) $statement->fetchOne();
    }
}
