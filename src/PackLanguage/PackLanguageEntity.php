<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\LanguagePack\PackLanguage;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Language\LanguageEntity;

class PackLanguageEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var bool
     */
    protected $administrationActive;

    /**
     * @var bool
     */
    protected $salesChannelActive;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var LanguageEntity
     */
    protected $language;

    public function isAdministrationActive(): bool
    {
        return $this->administrationActive;
    }

    public function setAdministrationActive(bool $administrationActive): void
    {
        $this->administrationActive = $administrationActive;
    }

    public function isSalesChannelActive(): bool
    {
        return $this->salesChannelActive;
    }

    public function setSalesChannelActive(bool $salesChannelActive): void
    {
        $this->salesChannelActive = $salesChannelActive;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getLanguage(): LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
