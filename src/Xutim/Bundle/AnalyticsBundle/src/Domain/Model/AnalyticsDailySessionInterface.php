<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailySessionInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getEntryPath(): string;

    public function getExitPath(): string;

    public function getSessionCount(): int;

    public function getTotalPageviews(): int;

    public function getBounces(): int;

    public function getTotalDurationSeconds(): int;

    public function getBounceRate(): float;

    public function getAvgPagesPerSession(): float;

    public function getAvgVisitDuration(): float;
}
