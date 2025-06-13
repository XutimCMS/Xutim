<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

#[MappedSuperclass]
class TagTranslation implements TagTranslationInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $slug;

    #[Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[ManyToOne(targetEntity: TagInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
    private TagInterface $tag;

    public function __construct(string $name, string $slug, string $locale, TagInterface $tag)
    {
        $this->id = Uuid::v4();
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->name = $name;
        $this->slug = $slug;
        $this->locale = $locale;
        $this->tag = $tag;
    }

    public function change(string $name, string $slug): void
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTag(): TagInterface
    {
        return $this->tag;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getObject(): TagInterface
    {
        return $this->tag;
    }

    public function getStatus(): PublicationStatus
    {
        return $this->tag->getStatus();
    }

    public function isInStatus(PublicationStatus $status): bool
    {
        return $this->tag->isInStatus($status);
    }

    public function isPublished(): bool
    {
        return $this->tag->isPublished();
    }
}
