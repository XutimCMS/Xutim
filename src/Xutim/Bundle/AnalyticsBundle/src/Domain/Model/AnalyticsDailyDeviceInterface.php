<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

interface AnalyticsDailyDeviceInterface
{
    public function getDate(): \DateTimeImmutable;

    public function getDeviceType(): string;

    public function getBrowser(): string;

    public function getOs(): string;

    public function getVisits(): int;

    public function getUniqueVisitors(): int;
}
