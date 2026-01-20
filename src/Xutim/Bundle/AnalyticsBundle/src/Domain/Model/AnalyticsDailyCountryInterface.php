<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailyCountryInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getCountry(): string;

    public function getVisits(): int;

    public function getUniqueVisitors(): int;
}
