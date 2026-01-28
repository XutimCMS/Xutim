<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

#[UniqueConstraint(columns: ['locale', 'slug'])]
trait ContentTranslationTrait
{
    #[Column(type: 'string', length: 255, nullable: false)]
    private string $preTitle;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $subTitle;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $slug;

    /**
     * @var EditorBlock
     */
    #[Column(type: Types::JSON, nullable: false)]
    private array $content;

    #[Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[Column(type: 'text', nullable: false)]
    private string $description;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Reference translation updatedAt at last save of this translation.'])]
    private ?DateTimeImmutable $referenceSyncedAt = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $searchContent = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $searchTagContent = null;

    #[Column(type: 'text', nullable: true, options: ['default' => null, 'comment' => 'tsvector for fulltext search'], insertable: false, updatable: false)]
    private ?string $searchVector = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $editingUser = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $editingHeartbeatAt = null;

    /**
     * @param EditorBlock $content
     */
    public function change(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description
    ): void {
        $this->updatedAt = new DateTimeImmutable();
        $this->preTitle = $preTitle;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->slug = $slug;
        $this->content = $content;
        $this->locale = $locale;
        $this->description = $description;
    }

    public function getLocale(): string
    {
        return $this->locale;
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
     * @phpstan-assert-if-true NonEmptyEditorBlock $this->content
     * @phpstan-assert-if-false array{ time: int, version: string, blocks: array{}} $this->content
     */
    public function hasContent(): bool
    {
        if (count($this->content) === 0) {
            return false;
        }

        if (array_key_exists('blocks', $this->content) === false) {
            return false;
        }

        if (count($this->content['blocks']) === 0) {
            return false;
        }

        return true;
    }

    public function getReferenceSyncedAt(): ?DateTimeImmutable
    {
        return $this->referenceSyncedAt;
    }

    public function changeReferenceSyncedAt(?DateTimeImmutable $at): void
    {
        $this->referenceSyncedAt = $at;
    }

    public function changeSearchContent(string $content): void
    {
        $this->searchContent = $content;
    }

    public function changeSearchTagContent(string $content): void
    {
        $this->searchTagContent = $content;
    }

    public function startEditing(UserInterface $user): void
    {
        $this->editingUser = $user;
        $this->editingHeartbeatAt = new DateTimeImmutable();
    }

    public function heartbeat(): void
    {
        $this->editingHeartbeatAt = new DateTimeImmutable();
    }

    public function stopEditing(): void
    {
        $this->editingUser = null;
        $this->editingHeartbeatAt = null;
    }

    public function getEditingUser(): ?UserInterface
    {
        return $this->editingUser;
    }

    public function getEditingHeartbeatAt(): ?DateTimeImmutable
    {
        return $this->editingHeartbeatAt;
    }

    public function isBeingEditedBy(?UserInterface $excludeUser = null, int $timeoutSeconds = 30): bool
    {
        if ($this->editingUser === null || $this->editingHeartbeatAt === null) {
            return false;
        }

        $elapsed = time() - $this->editingHeartbeatAt->getTimestamp();
        if ($elapsed > $timeoutSeconds) {
            return false;
        }

        if ($excludeUser !== null && $this->editingUser->getId()->equals($excludeUser->getId())) {
            return false;
        }

        return true;
    }
}
