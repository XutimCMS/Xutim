<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyDeviceInterface;

interface AnalyticsDailyDeviceRepositoryInterface
{
    public function save(AnalyticsDailyDeviceInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{deviceType: string, browser: string, os: string, visits: int, uniqueVisitors: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<array{deviceType: string, browser: string, os: string, visits: int, uniqueVisitors: int}>
     */
    public function findByDateRange(DateRange $range): array;

    /**
     * @return list<array{deviceType: string, visits: int, uniqueVisitors: int}>
     */
    public function findDeviceTypeBreakdown(DateRange $range): array;

    /**
     * @return list<array{browser: string, visits: int, uniqueVisitors: int}>
     */
    public function findBrowserBreakdown(DateRange $range): array;

    /**
     * @return list<array{os: string, visits: int, uniqueVisitors: int}>
     */
    public function findOsBreakdown(DateRange $range): array;

    public function getTableName(): string;
}
