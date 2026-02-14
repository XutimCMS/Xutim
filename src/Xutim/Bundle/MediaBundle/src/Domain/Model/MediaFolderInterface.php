<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

interface MediaFolderInterface
{
    public function id(): Uuid;

    public function code(): string;

    public function name(): string;

    public function basePath(): string;

    public function isActive(): bool;

    public function parent(): ?self;

    /**
     * @return Collection<int, self>
     */
    public function children(): Collection;

    /**
     * @return Collection<int, MediaInterface>
     */
    public function media(): Collection;

    /**
     * @return array<int, self>
     */
    public function folderPath(): array;

    public function createdAt(): DateTimeImmutable;

    public function updatedAt(): DateTimeImmutable;

    public function change(?string $name, ?string $basePath, ?bool $active, ?self $parent): void;

    public function changeName(string $name): void;
}
