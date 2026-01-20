<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailyTrafficSourceInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getSource(): string;

    public function getVisits(): int;

    public function getUniqueVisitors(): int;
}
