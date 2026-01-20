<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailySummaryInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getPath(): string;

    public function getPageviews(): int;

    public function getUniqueVisitors(): int;

    public function getAvgScrollDepth(): ?float;

    public function getAvgLoadTime(): ?float;
}
