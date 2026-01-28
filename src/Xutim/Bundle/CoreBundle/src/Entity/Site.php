<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\SiteInterface;
use Xutim\CoreBundle\Dto\SiteDto;

#[MappedSuperclass]
class Site implements SiteInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['comment' => 'Site\'s languages.'])]
    private array $locales;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['comment' => 'Site\'s extended content languages.'])]
    private array $extendedContentLocales;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s public theme.'])]
    private string $theme;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s sender\'s email address.'])]
    private string $sender;

    #[Column(type: 'string', length: 10, nullable: false, options: ['comment' => 'Site\'s reference locale for translations.'])]
    private string $referenceLocale;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->locales = ['en', 'fr'];
        $this->extendedContentLocales = ['en', 'fr'];
        $this->theme = 'default';
        $this->sender = 'website@example.com';
        $this->referenceLocale = 'en';
    }

    /**
     * @param array<string> $locales
     * @param array<string> $extendedContentLocales
     */
    public function change(array $locales, array $extendedContentLocales, string $theme, string $sender, string $referenceLocale): void
    {
        usort($locales, fn ($l1, $l2) => Languages::getName($l1) <=> Languages::getName($l2));
        usort($extendedContentLocales, fn ($l1, $l2) => Languages::getName($l1) <=> Languages::getName($l2));
        $this->locales = $locales;
        $this->extendedContentLocales = $extendedContentLocales;
        $this->theme = $theme;
        $this->sender = $sender;
        $this->referenceLocale = $referenceLocale;
    }

    public function getReferenceLocale(): string
    {
        return $this->referenceLocale;
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @return array<string>
     */
    public function getContentLocales(): array
    {
        return $this->extendedContentLocales;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function toDto(): SiteDto
    {
        return new SiteDto($this->locales, $this->extendedContentLocales, $this->theme, $this->sender, $this->referenceLocale);
    }
}
