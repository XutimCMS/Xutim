<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\PublicationStatus;

interface ContentTranslationInterface
{
    public function __toString(): string;

    /**
     * @param EditorBlock $content
     */
    public function change(string $preTitle, string $title, string $subTitle, string $slug, array $content, string $locale, string $description): void;

    public function getId(): Uuid;

    public function hasPage(): bool;

    public function hasArticle(): bool;

    public function getPage(): PageInterface;

    public function getArticle(): ArticleInterface;

    public function getObject(): ArticleInterface|PageInterface;

    public function getLocale(): string;

    public function getPreTitle(): string;

    public function getTitle(): string;

    public function getSubTitle(): string;

    public function getSlug(): string;

    public function getDescription(): string;

    /**
     * @return EditorBlock
     */
    public function getContent(): array;

    public function hasContent(): bool;

    public function hasUntranslatedChange(): bool;

    public function newTranslationChange(): void;

    public function changeSearchContent(string $content): void;

    public function changeSearchTagContent(string $content): void;

    public function getStatus(): PublicationStatus;

    public function changeStatus(PublicationStatus $status): void;

    public function isInStatus(PublicationStatus $status): bool;

    public function isPublished(): bool;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function changePublishedAt(?DateTimeImmutable $publishedAt): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function setPublishedAt(?DateTimeImmutable $date): void;
}
