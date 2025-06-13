<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Entity\PublicationStatus;

interface EventInterface
{
    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function getStatus(): PublicationStatus;

    public function changeStatus(PublicationStatus $status): void;

    public function isInStatus(PublicationStatus $status): bool;

    public function isPublished(): bool;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function changePublishedAt(?DateTimeImmutable $publishedAt): void;

    /**
     * @return ?EventTranslationInterface
     */
    public function getTranslationByLocale(string $locale);

    /**
     * @return EventTranslationInterface
     */
    public function getTranslationByLocaleOrAny(string $locale);

    public function changeDates(DateTimeImmutable $startsAt, DateTimeImmutable $endsAt): void;
    public function changeArticle(?ArticleInterface $article): void;

    public function changePage(?PageInterface $page): void;

    public function addTranslation(EventTranslationInterface $trans): void;

    public function getId(): Uuid;

    public function setPublishedAt(?DateTimeImmutable $date): void;

    public function getStartsAt(): DateTimeImmutable;

    public function getEndsAt(): DateTimeImmutable;

    public function hasPage(): bool;

    public function hasArticle(): bool;

    public function getPage(): ?PageInterface;

    public function getArticle(): ?ArticleInterface;

    /**
     * @return Collection<int, EventTranslationInterface>
    */
    public function getTranslations(): Collection;
}
