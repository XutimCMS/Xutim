<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class AnalyticsDailySession implements AnalyticsDailySessionInterface
{
    #[Id]
    #[Column(type: 'date_immutable')]
    private readonly \DateTimeImmutable $date;

    #[Id]
    #[Column(type: 'text')]
    private readonly string $entryPath;

    #[Id]
    #[Column(type: 'text')]
    private readonly string $exitPath;

    #[Column(type: 'integer')]
    private readonly int $sessionCount;

    #[Column(type: 'integer')]
    private readonly int $totalPageviews;

    #[Column(type: 'integer')]
    private readonly int $bounces;

    #[Column(type: 'integer')]
    private readonly int $totalDurationSeconds;

    public function __construct(
        \DateTimeImmutable $date,
        string $entryPath,
        string $exitPath,
        int $sessionCount,
        int $totalPageviews,
        int $bounces,
        int $totalDurationSeconds
    ) {
        $this->date = $date;
        $this->entryPath = $entryPath;
        $this->exitPath = $exitPath;
        $this->sessionCount = $sessionCount;
        $this->totalPageviews = $totalPageviews;
        $this->bounces = $bounces;
        $this->totalDurationSeconds = $totalDurationSeconds;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getEntryPath(): string
    {
        return $this->entryPath;
    }

    public function getExitPath(): string
    {
        return $this->exitPath;
    }

    public function getSessionCount(): int
    {
        return $this->sessionCount;
    }

    public function getTotalPageviews(): int
    {
        return $this->totalPageviews;
    }

    public function getBounces(): int
    {
        return $this->bounces;
    }

    public function getTotalDurationSeconds(): int
    {
        return $this->totalDurationSeconds;
    }

    public function getBounceRate(): float
    {
        if ($this->sessionCount === 0) {
            return 0.0;
        }

        return $this->bounces / $this->sessionCount;
    }

    public function getAvgPagesPerSession(): float
    {
        if ($this->sessionCount === 0) {
            return 0.0;
        }

        return $this->totalPageviews / $this->sessionCount;
    }

    public function getAvgVisitDuration(): float
    {
        if ($this->sessionCount === 0) {
            return 0.0;
        }

        return $this->totalDurationSeconds / $this->sessionCount;
    }
}
