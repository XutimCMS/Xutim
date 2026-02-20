<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\MediaBundle\Domain\Model\MediaInterface;

interface TagInterface
{
    public function __toString(): string;

    public function change(Color $color, ?MediaInterface $image): void;

    public function addTranslation(TagTranslationInterface $trans): void;

    public function toggleExcludeFromNews(): void;

    public function getId(): Uuid;

    public function getFeaturedImage(): ?MediaInterface;

    public function getColor(): Color;

    public function getLayout(): ?string;

    public function changeLayout(?string $layout): void;

    /**
     * @return Collection<int, TagTranslationInterface>
     */
    public function getTranslations(): Collection;

    /**
     * @return Collection<int, ArticleInterface>
     */
    public function getArticles(): Collection;

    public function isExcludeFromNews(): bool;

    public function getStatus(): PublicationStatus;

    public function changeStatus(PublicationStatus $status): void;

    public function isInStatus(PublicationStatus $status): bool;

    public function isPublished(): bool;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function changePublishedAt(?DateTimeImmutable $publishedAt): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function isArchived(): bool;

    public function archive(): void;

    /**
     * @return ?TagTranslationInterface
     */
    public function getTranslationByLocale(string $locale);

    /**
     * @return TagTranslationInterface
     */
    public function getTranslationByLocaleOrAny(string $locale);
}
