<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

#[MappedSuperclass()]
abstract class Redirect implements RedirectInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    protected Uuid $id;

    #[Column(type: Types::STRING)]
    protected string $source;

    #[Column(type: Types::STRING, nullable: true)]
    protected ?string $locale;

    #[Column(type: Types::BOOLEAN)]
    protected bool $permanent;

    #[ManyToOne(targetEntity: ContentTranslationInterface::class)]
    #[JoinColumn(nullable: false)]
    protected ContentTranslationInterface $targetContentTranslation;

    public function __construct(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false
    ) {
        $this->id = Uuid::v4();
        $this->change($source, $targetContentTranslation, $locale, $permanent);
    }

    public function change(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ): void {
        $this->source = $source;
        $this->targetContentTranslation = $targetContentTranslation;
        $this->locale = $locale;
        $this->permanent = $permanent;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTargetUrl(): string
    {
        return '';
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTargetContentTranslation(): ContentTranslationInterface
    {
        return $this->targetContentTranslation;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }
}
