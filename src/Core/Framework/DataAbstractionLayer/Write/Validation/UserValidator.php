<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\Framework\DataAbstractionLayer\Write\Validation;

use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\User\UserDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 */
class UserValidator extends AbstractLanguageValidator
{
    protected function getSupportedEntity(): string
    {
        return UserDefinition::ENTITY_NAME;
    }

    protected function validate(WriteCommand $command, ConstraintViolationList $violationList): void
    {
        $payload = $command->getPayload();
        if (!isset($payload['locale_id'])) {
            return;
        }

        $localeId = $payload['locale_id'];
        if (!$this->isLocaleManagedByLanguagePack($localeId) || $this->getAdministrationActiveByLocale($localeId)) {
            return;
        }

        $violationList->add(
            new ConstraintViolation(
                \sprintf('The language bound to the locale with the id "%s" is disabled for the Administration.', Uuid::fromBytesToHex($localeId)),
                'The language with the id "{{ languageId }}" is disabled for the Administration.',
                [$localeId],
                null,
                $command->getPath(),
                $localeId,
            ),
        );
    }

    private function getAdministrationActiveByLocale(string $localeId): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('packLanguage.administration_active')
            ->from(PackLanguageDefinition::ENTITY_NAME, 'packLanguage')
            ->leftJoin(
                'packLanguage',
                LanguageDefinition::ENTITY_NAME,
                'language',
                'packLanguage.language_id = language.id',
            )
            ->where('language.locale_id = :localeId')
            ->setParameter('localeId', $localeId)
            ->setMaxResults(1)
            ->executeQuery();

        return (bool) $statement->fetchOne();
    }

    private function isLocaleManagedByLanguagePack(string $localeId): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('swag_language_pack_language_id')
            ->from(LanguageDefinition::ENTITY_NAME)
            ->where('locale_id = :localeId')
            ->setParameter('localeId', $localeId)
            ->setMaxResults(1)
            ->executeQuery();

        return (bool) $statement->fetchOne();
    }
}
