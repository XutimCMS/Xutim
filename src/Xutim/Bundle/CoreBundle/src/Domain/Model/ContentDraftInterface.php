<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\DraftStatus;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

interface ContentDraftInterface
{
    public function getId(): Uuid;

    public function getTranslation(): ContentTranslationInterface;

    public function getUser(): ?UserInterface;

    public function isLiveVersion(): bool;

    public function getStatus(): DraftStatus;

    public function changeStatus(DraftStatus $status): void;

    public function markAsLive(): void;

    public function markAsStale(): void;

    public function markAsDiscarded(): void;

    public function getBasedOnDraft(): ?self;

    public function getPreTitle(): string;

    public function getTitle(): string;

    public function getSubTitle(): string;

    public function getSlug(): string;

    public function getDescription(): string;

    /**
     * @return EditorBlock
     */
    public function getContent(): array;

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
    ): void;

    public function snapshotFromTranslation(): void;

    public function applyToTranslation(): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
