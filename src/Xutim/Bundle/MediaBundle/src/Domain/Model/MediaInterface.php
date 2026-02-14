<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

interface MediaInterface
{
    public function id(): Uuid;

    public function folder(): ?MediaFolderInterface;

    public function originalPath(): string;

    public function originalExt(): string;

    public function mime(): string;

    public function hash(): string;

    public function sizeBytes(): int;

    public function width(): int;

    public function height(): int;

    public function copyright(): ?string;

    public function focalX(): ?float;

    public function focalY(): ?float;

    public function blurHash(): ?string;

    public function isImage(): bool;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function change(
        ?string $copyright,
        ?float $focalX,
        ?float $focalY,
        ?MediaFolderInterface $folder,
        ?string $blurHash,
    ): void;

    public function changeFolder(?MediaFolderInterface $folder): void;

    public function changeCopyright(string $copyright): void;

    public function changeBlurHash(string $blurHash): void;

    public function getTranslationByLocale(string $locale): ?MediaTranslationInterface;

    public function addTranslation(MediaTranslationInterface $translation): void;

    /**
     * @return Collection<int, MediaTranslationInterface>
     */
    public function getTranslations(): Collection;
}
