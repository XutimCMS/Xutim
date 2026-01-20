<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailySummary implements AnalyticsDailySummaryInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'text')]
    private readonly string $path;

    #[Column(type: 'integer')]
    private readonly int $pageviews;

    #[Column(type: 'integer')]
    private readonly int $uniqueVisitors;

    #[Column(type: 'float', nullable: true)]
    private readonly ?float $avgScrollDepth;

    #[Column(type: 'float', nullable: true)]
    private readonly ?float $avgLoadTime;

    public function __construct(
        \DateTimeImmutable $date,
        string $path,
        int $pageviews,
        int $uniqueVisitors,
        ?float $avgScrollDepth,
        ?float $avgLoadTime
    ) {
        $this->date = $date;
        $this->path = $path;
        $this->pageviews = $pageviews;
        $this->uniqueVisitors = $uniqueVisitors;
        $this->avgScrollDepth = $avgScrollDepth;
        $this->avgLoadTime = $avgLoadTime;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPageviews(): int
    {
        return $this->pageviews;
    }

    public function getUniqueVisitors(): int
    {
        return $this->uniqueVisitors;
    }

    public function getAvgScrollDepth(): ?float
    {
        return $this->avgScrollDepth;
    }

    public function getAvgLoadTime(): ?float
    {
        return $this->avgLoadTime;
    }
}
