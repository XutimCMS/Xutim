<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

interface ContentBlockInterface
{
    public function getId(): Uuid;

    public function getType(): string;

    public function getDraft(): ContentDraftInterface;

    public function getParent(): ?ContentBlockInterface;

    public function setParent(?ContentBlockInterface $parent): void;

    public function getSlot(): ?int;

    public function setSlot(?int $slot): void;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
