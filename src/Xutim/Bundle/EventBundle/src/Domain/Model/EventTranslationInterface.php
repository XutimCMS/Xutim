<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

interface EventTranslationInterface
{
    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function change(
        string $title,
        string $location,
        string $description,
    ): void;

    public function getId(): Uuid;

    public function getTitle(): string;

    public function getLocation(): string;

    public function getDescription(): string;

    public function getLocale(): string;

    public function getEvent(): EventInterface;
}
