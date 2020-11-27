<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\Test\Core\Framework\DataAbstractionLayer\Write\Validation;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\User\UserDefinition;
use Swag\LanguagePack\Test\Helper\ServicesTrait;

class UserValidatorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var EntityRepositoryInterface
     */
    private $userRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepositoryInterface $userRepository */
        $userRepository = $container->get(\sprintf('%s.repository', UserDefinition::ENTITY_NAME));
        $this->userRepository = $userRepository;
    }

    public function testCreatingASalesChannelDomainWithADeactivatedLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $localeId = $this->setSalesChannelActiveForLanguageByLocale('da-DK', false, $context);

        $this->expectExceptionMessage(\sprintf('The language bound to the locale with the id "%s" is disabled for all Sales Channels.', $localeId));
        $this->createUser(Uuid::randomHex(), $localeId, $context);
    }

    public function testUpdatingASalesChannelDomainToADisabledLanguageFails(): void
    {
        $context = Context::createDefaultContext();
        $enabledLocaleId = $this->setSalesChannelActiveForLanguageByLocale('da-DK', true, $context);
        $disabledLocaleId = $this->setSalesChannelActiveForLanguageByLocale('fr-FR', false, $context);

        $userId = Uuid::randomHex();
        $this->createUser($userId, $enabledLocaleId, $context);

        $criteria = new Criteria([$userId]);

        /** @var SalesChannelDomainEntity|null $user */
        $user = $this->userRepository->search($criteria, $context)->first();
        static::assertNotNull($user);

        $this->expectExceptionMessage(\sprintf('The language bound to the locale with the id "%s" is disabled for all Sales Channels.', $disabledLocaleId));
        $this->userRepository->update([
            [
                'id' => $userId,
                'localeId' => $disabledLocaleId,
            ],
        ], $context);
    }

    private function createUser(string $userId, string $localeId, Context $context): void
    {
        $this->userRepository->create([
            [
                'id' => $userId,
                'localeId' => $localeId,
                'username' => 'foo',
                'password' => 'foobarbatz',
                'firstName' => 'Olaf',
                'lastName' => 'Olafson',
                'email' => 'test@example.com',
            ],
        ], $context);
    }
}
