<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

#[MappedSuperclass]
class ContentDraft implements ContentDraftInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: ContentTranslationInterface::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ContentTranslationInterface $translation;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $user;

    #[Column(type: Types::STRING, length: 20, enumType: DraftStatus::class)]
    private DraftStatus $status;

    #[ManyToOne(targetEntity: ContentDraftInterface::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ContentDraftInterface $basedOnDraft;

    /** @var Collection<int, ContentBlockInterface> */
    #[OneToMany(mappedBy: 'draft', targetEntity: ContentBlockInterface::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $blocks;

    public function __construct(
        ContentTranslationInterface $translation,
        ?UserInterface $user = null,
        ?ContentDraftInterface $basedOnDraft = null,
    ) {
        $this->id = Uuid::v4();
        $this->translation = $translation;
        $this->user = $user;
        $this->basedOnDraft = $basedOnDraft;
        $this->status = $user === null ? DraftStatus::LIVE : DraftStatus::EDITING;
        $this->blocks = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTranslation(): ContentTranslationInterface
    {
        return $this->translation;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function isLiveVersion(): bool
    {
        return $this->user === null && $this->status->isLive();
    }

    public function getStatus(): DraftStatus
    {
        return $this->status;
    }

    public function changeStatus(DraftStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsLive(): void
    {
        $this->changeStatus(DraftStatus::LIVE);
    }

    public function markAsStale(): void
    {
        $this->changeStatus(DraftStatus::STALE);
    }

    public function markAsDiscarded(): void
    {
        $this->changeStatus(DraftStatus::DISCARDED);
    }

    public function getBasedOnDraft(): ?ContentDraftInterface
    {
        return $this->basedOnDraft;
    }

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getTopLevelBlocks(): Collection
    {
        return $this->blocks->filter(
            fn (ContentBlockInterface $block) => $block->getParent() === null
        );
    }

    public function addBlock(ContentBlockInterface $block): void
    {
        if ($this->blocks->contains($block)) {
            return;
        }
        $this->blocks->add($block);
    }

    public function removeBlock(ContentBlockInterface $block): void
    {
        $this->blocks->removeElement($block);
    }
}
