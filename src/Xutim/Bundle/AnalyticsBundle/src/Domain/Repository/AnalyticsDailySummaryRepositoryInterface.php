<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailySummaryInterface;

interface AnalyticsDailySummaryRepositoryInterface
{
    public function save(AnalyticsDailySummaryInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{path: string, pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<AnalyticsDailySummaryInterface>
     */
    public function findByDateRange(DateRange $range): array;

    /**
     * @return list<array{path: string, totalPageviews: int, totalVisitors: int}>
     */
    public function findTopPagesByDateRange(DateRange $range, int $limit = 20): array;

    public function getTotalPageviews(DateRange $range): int;

    public function getTotalUniqueVisitors(DateRange $range): int;

    public function getAverageScrollDepth(DateRange $range): ?float;

    public function getAverageLoadTime(DateRange $range): ?float;

    /**
     * @return list<array{date: string, pageviews: int}>
     */
    public function getPageviewsByDay(DateRange $range): array;

    /**
     * @return list<array{date: string, pageviews: int}>
     */
    public function getPageviewsByDayForPath(string $path, DateRange $range): array;

    /**
     * @return array{pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}|null
     */
    public function getPageStats(string $path, DateRange $range): ?array;

    public function getTableName(): string;
}
