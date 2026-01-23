<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\EditorBundle\Entity\DraftStatus;
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

    public function getBasedOnDraft(): ?ContentDraftInterface;

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getBlocks(): Collection;

    /**
     * @return Collection<int, ContentBlockInterface>
     */
    public function getTopLevelBlocks(): Collection;

    public function addBlock(ContentBlockInterface $block): void;

    public function removeBlock(ContentBlockInterface $block): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
