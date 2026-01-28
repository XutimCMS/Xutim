<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface as BaseContentDraftInterface;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;

#[Entity]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'type', type: 'string', length: 50)]
abstract class ContentBlock implements ContentBlockInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: BaseContentDraftInterface::class, inversedBy: 'blocks')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ContentDraftInterface $draft;

    #[ManyToOne(targetEntity: ContentBlockInterface::class)]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?ContentBlockInterface $parent = null;

    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $slot = null;

    #[Column(type: Types::INTEGER)]
    private int $position = 0;

    public function __construct(
        ContentDraftInterface $draft,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        $this->id = Uuid::v4();
        $this->draft = $draft;
        $this->parent = $parent;
        $this->slot = $slot;
        $this->position = $position;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();

        $draft->addBlock($this);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    abstract public function getType(): string;

    public function getDraft(): ContentDraftInterface
    {
        return $this->draft;
    }

    public function getParent(): ?ContentBlockInterface
    {
        return $this->parent;
    }

    public function setParent(?ContentBlockInterface $parent): void
    {
        $this->parent = $parent;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSlot(): ?int
    {
        return $this->slot;
    }

    public function setSlot(?int $slot): void
    {
        $this->slot = $slot;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
        $this->updatedAt = new DateTimeImmutable();
    }
}
