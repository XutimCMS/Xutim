<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Service;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;

final class DateRangeResolver
{
    public const string PRESET_TODAY = 'today';
    public const string PRESET_7D = '7d';
    public const string PRESET_30D = '30d';
    public const string PRESET_CUSTOM = 'custom';
    public const string DEFAULT_PRESET = self::PRESET_7D;

    public function resolve(string $preset, ?string $from = null, ?string $to = null): DateRange
    {
        return match ($preset) {
            self::PRESET_TODAY => DateRange::today(),
            self::PRESET_7D => DateRange::last7Days(),
            self::PRESET_30D => DateRange::last30Days(),
            self::PRESET_CUSTOM => $this->resolveCustom($from, $to),
            default => DateRange::last7Days(),
        };
    }

    private function resolveCustom(?string $from, ?string $to): DateRange
    {
        try {
            $fromDate = $from !== null ? new \DateTimeImmutable($from) : new \DateTimeImmutable('-7 days');
            $toDate = $to !== null ? new \DateTimeImmutable($to) : new \DateTimeImmutable();

            return DateRange::custom($fromDate, $toDate);
        } catch (\Exception) {
            return DateRange::last7Days();
        }
    }
}
