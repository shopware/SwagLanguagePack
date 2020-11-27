<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Core\Framework\DataAbstractionLayer\Write\Validation;

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\User\UserDefinition;
use Swag\LanguagePack\PackLanguage\PackLanguageDefinition;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UserValidator extends AbstractLanguageValidator
{
    protected function getSupportedCommandDefinitionClass(): string
    {
        return UserDefinition::class;
    }

    protected function validate(WriteCommand $command, ConstraintViolationList $violationList): void
    {
        $payload = $command->getPayload();
        if (!isset($payload['locale_id']) || $this->getSalesChannelActiveByLocale($payload['locale_id'])) {
            return;
        }

        $violationList->add(
            new ConstraintViolation(
                \sprintf('The language bound to the locale with the id "%s" is disabled for all Sales Channels.', Uuid::fromBytesToHex($payload['locale_id'])),
                'The language with the id "{{ languageId }}" is disabled for all Sales Channels.',
                [$payload['locale_id']],
                null,
                $command->getPath(),
                $payload['locale_id']
            )
        );
    }

    private function getSalesChannelActiveByLocale(string $localeId): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('packLanguage.sales_channel_active')
            ->from(PackLanguageDefinition::ENTITY_NAME, 'packLanguage')
            ->leftJoin(
                'packLanguage',
                LanguageDefinition::ENTITY_NAME,
                'language',
                'packLanguage.language_id = language.id'
            )
            ->where('language.locale_id = :localeId')
            ->setParameter('localeId', $localeId)
            ->setMaxResults(1)
            ->execute();

        if (!$statement instanceof ResultStatement) {
            return false;
        }

        return (bool) $statement->fetch(FetchMode::COLUMN);
    }
}
