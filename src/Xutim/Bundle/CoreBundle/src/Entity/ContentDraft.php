<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
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

    #[Column(type: Types::STRING, length: 255)]
    private string $preTitle;

    #[Column(type: Types::STRING, length: 255)]
    private string $title;

    #[Column(type: Types::STRING, length: 255)]
    private string $subTitle;

    #[Column(type: Types::STRING, length: 255)]
    private string $slug;

    #[Column(type: Types::TEXT)]
    private string $description;

    /**
     * @var EditorBlock
     */
    #[Column(type: Types::JSON)]
    private array $content;

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
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->snapshotFromTranslation();
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

    public function getPreTitle(): string
    {
        return $this->preTitle;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return EditorBlock
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param EditorBlock $content
     */
    public function changeContent(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $description,
    ): void {
        $this->preTitle = $preTitle;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->slug = $slug;
        $this->content = $content;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function snapshotFromTranslation(): void
    {
        $this->preTitle = $this->translation->getPreTitle();
        $this->title = $this->translation->getTitle();
        $this->subTitle = $this->translation->getSubTitle();
        $this->slug = $this->translation->getSlug();
        $this->content = $this->translation->getContent();
        $this->description = $this->translation->getDescription();
    }

    public function applyToTranslation(): void
    {
        $this->translation->change(
            $this->preTitle,
            $this->title,
            $this->subTitle,
            $this->slug,
            $this->content,
            $this->translation->getLocale(),
            $this->description,
        );
    }
}
