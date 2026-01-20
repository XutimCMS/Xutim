<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyPageReferrerInterface;

interface AnalyticsDailyPageReferrerRepositoryInterface
{
    public function save(AnalyticsDailyPageReferrerInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{targetPath: string, referrer: string, isExternal: bool, visits: int, uniqueVisitors: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<array{referrer: string, isExternal: bool, visits: int, uniqueVisitors: int}>
     */
    public function findReferrersForPage(string $targetPath, DateRange $range, int $limit = 20): array;

    /**
     * @return list<array{referrer: string, visits: int, uniqueVisitors: int}>
     */
    public function findTopExternalReferrers(DateRange $range, int $limit = 20): array;

    public function getTableName(): string;
}
