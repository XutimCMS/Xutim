<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyCountryInterface;

interface AnalyticsDailyCountryRepositoryInterface
{
    public function save(AnalyticsDailyCountryInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{country: string, visits: int, uniqueVisitors: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<array{country: string, visits: int, uniqueVisitors: int}>
     */
    public function findTopCountriesByDateRange(DateRange $range, int $limit = 50): array;

    public function getTableName(): string;
}
