<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailyPageReferrerInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getTargetPath(): string;

    public function getReferrer(): string;

    public function isExternal(): bool;

    public function getVisits(): int;

    public function getUniqueVisitors(): int;
}
