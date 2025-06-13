<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Entity\Color;

interface PageInterface
{
    /**
     * @param list<string> $locales
     */
    public function change(?string $colorHex, array $locales, ?PageInterface $parent): void;

    public function addTranslation(ContentTranslationInterface $trans): void;

    public function changeParent(?PageInterface $parent): void;

    public function setDefaultTranslation(ContentTranslationInterface $trans): void;

    public function getId(): Uuid;

    public function getColor(): Color;

    /**
     * @return ContentTranslationInterface
     */
    public function getTranslationByLocaleOrDefault(string $locale);

    /**
     * @return ?ContentTranslationInterface
     */
    public function getPublishedTranslationByLocale(string $locale);

    /**
     * @return ?ContentTranslationInterface
     */
    public function getPublishedTranslationByLocaleOrAny(string $locale);

    /**
     * @return Collection<string, ContentTranslationInterface>
     */
    public function getPublishedTranslations(): Collection;

    /**
     * @return array<string, string>
     */
    public function getExistingTranslationLocales(): array;

    public function getDefaultTranslation(): ContentTranslationInterface;

    public function getRootPage(): PageInterface;

    public function getParent(): ?PageInterface;

    public function isRoot(): bool;

    /**
     * @return Collection<int, PageInterface>
     */
    public function getChildren(): Collection;

    /**
     * @return list<string>
     */
    public function getLocales(): array;

    /**
     * @return Collection<string, ContentTranslationInterface>
     */
    public function getTranslations(): Collection;

    public function canBeDeleted(): bool;

    public function prepareDeletion(): bool;

    public function getRootParent(): PageInterface;

    public function getPosition(): int;

    public function movePosUp(int $step): void;

    public function movePosDown(int $step): void;

    public function getLayout(): ?string;

    public function changeLayout(?Layout $layout): void;

    public function getFeaturedImage(): ?FileInterface;

    public function changeFeaturedImage(?FileInterface $image): void;

    public function hasFeaturedImage(): bool;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    /**
     * @return Collection<int, FileInterface>
     */
    public function getFiles(): Collection;

    /**
     * @return Collection<int, FileInterface>
     */
    public function getImages(): Collection;

    public function addFile(FileInterface $file): void;

    public function removeFile(FileInterface $file): void;

    public function getImage(): ?FileInterface;

    public function isArchived(): bool;

    public function archive(): void;

    /**
     * @return ContentTranslationInterface|null
    */
    public function getTranslationByLocale(string $locale);

    /**
     * @return ContentTranslationInterface
    */
    public function getTranslationByLocaleOrAny(string $locale);
}
