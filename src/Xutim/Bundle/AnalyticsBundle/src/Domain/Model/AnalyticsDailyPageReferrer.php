<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailyPageReferrer implements AnalyticsDailyPageReferrerInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'text')]
    private readonly string $targetPath;

    #[Id]
    #[Column(type: 'text')]
    private readonly string $referrer;

    #[Id]
    #[Column(type: 'boolean')]
    private readonly bool $isExternal;

    #[Column(type: 'integer')]
    private readonly int $visits;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    public function __construct(
        \DateTimeImmutable $date,
        string $targetPath,
        string $referrer,
        bool $isExternal,
        int $visits,
        int $uniqueVisitors
    ) {
        $this->date = $date;
        $this->targetPath = $targetPath;
        $this->referrer = $referrer;
        $this->isExternal = $isExternal;
        $this->visits = $visits;
        $this->uniqueVisitors = $uniqueVisitors;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function getUniqueVisitors(): int
    {
        return $this->uniqueVisitors;
    }
}
