<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Layout;

interface ArticleInterface
{
    public function change(): void;

    public function getId(): Uuid;

    public function addTranslation(ContentTranslationInterface $trans): void;

    /**
     * @return Collection<string, ContentTranslationInterface>
     */
    public function getTranslations(): Collection;

    public function getDefaultTranslation(): ContentTranslationInterface;

    public function setDefaultTranslation(ContentTranslationInterface $trans): void;

    /**
     * @return array{total: int, translated: int}
     */
    public function getTranslationStats(): array;

    public function getTitle(): string;

    public function getLayout(): ?string;
    
    public function changeLayout(?Layout $layout): void;

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

    public function canBeDeleted(): bool;

    public function prepareDeletion(): bool;

    public function setPublishedAt(?DateTimeImmutable $date): void;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function canBePublished(): bool;

    public function isPublishingScheduled(): bool;

    public function getFeaturedImage(): ?FileInterface;

    public function changeFeaturedImage(?FileInterface $image): void;

    public function hasFeaturedImage(): bool;

    /**
     * @return Collection<int,TagInterface>
    */
    public function getTags(): Collection;

    public function hasTag(TagInterface $tag): bool;

    public function addTag(TagInterface $tag): void;

    public function removeTag(TagInterface $tag): void;

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
