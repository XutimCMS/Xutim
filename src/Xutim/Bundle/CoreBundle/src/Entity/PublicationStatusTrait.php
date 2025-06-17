<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Xutim\CoreBundle\Infra\Doctrine\Type\PublicationStatusType;

trait PublicationStatusTrait
{
    #[Column(type: PublicationStatusType::NAME, nullable: false)]
    private PublicationStatus $status;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $publishedAt;

    public function getStatus(): PublicationStatus
    {
        return $this->status;
    }

    public function changeStatus(PublicationStatus $status): void
    {
        $this->status = $status;
    }

    public function isInStatus(PublicationStatus $status): bool
    {
        return $this->status === $status;
    }

    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }

    public function isScheduled(): bool
    {
        return $this->status->isScheduled();
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function changePublishedAt(?DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }
}
