<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Repository\SiteRepository;

#[Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['comment' => 'Site\'s languages.'])]
    private array $locales;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s public theme.'])]
    private string $theme;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s sender\'s email address.'])]
    private string $sender;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->locales = ['en', 'fr'];
        $this->theme = 'tailwind';
        $this->sender = 'website@example.com';
    }

    /**
     * @param array<string> $locales
     */
    public function change(array $locales, string $theme, string $sender): void
    {
        $this->locales = $locales;
        $this->theme = $theme;
        $this->sender = $sender;
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function toDto(): SiteDto
    {
        return new SiteDto($this->locales, $this->theme, $this->sender);
    }
}
