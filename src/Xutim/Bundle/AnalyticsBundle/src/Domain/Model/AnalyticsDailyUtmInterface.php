<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailyUtmInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getUtmSource(): string;

    public function getUtmMedium(): string;

    public function getUtmCampaign(): string;

    public function getVisits(): int;

    public function getUniqueVisitors(): int;
}
