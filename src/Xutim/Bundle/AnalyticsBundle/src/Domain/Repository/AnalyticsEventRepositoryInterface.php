<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;

interface AnalyticsEventRepositoryInterface
{
    public function save(AnalyticsEventInterface $entity, bool $andFlush = false): void;

    public function remove(AnalyticsEventInterface $entity, bool $andFlush = false): void;

    public function getTableName(): string;

    public function countEventsForDate(\DateTimeImmutable $date): int;

    /**
     * @return list<array{path: string, pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}>
     */
    public function getDailySummaryAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{referer: string|null, sessionBucket: string}>
     */
    public function getEventsForTrafficSourceAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{country: string, visits: int, uniqueVisitors: int}>
     */
    public function getDailyCountryAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{userAgent: string|null, sessionBucket: string}>
     */
    public function getEventsForDeviceAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{utmSource: string, utmMedium: string, utmCampaign: string, visits: int, uniqueVisitors: int}>
     */
    public function getDailyUtmAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{path: string, referer: string, sessionBucket: string}>
     */
    public function getEventsForPageReferrerAggregation(\DateTimeImmutable $date): array;

    /**
     * @return list<array{sessionBucket: string, path: string, recordedAt: \DateTimeImmutable}>
     */
    public function getEventsForSessionAggregation(\DateTimeImmutable $date): array;

    public function getEarliestEventDate(): ?\DateTimeImmutable;

    public function getLatestEventDate(): ?\DateTimeImmutable;

    public function countEventsOlderThan(\DateTimeImmutable $cutoffDate): int;

    public function deleteEventsOlderThan(\DateTimeImmutable $cutoffDate, int $limit): int;
}
