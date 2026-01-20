<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyTrafficSourceInterface;

interface AnalyticsDailyTrafficSourceRepositoryInterface
{
    public function save(AnalyticsDailyTrafficSourceInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{source: string, visits: int, uniqueVisitors: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<array{source: string, visits: int, uniqueVisitors: int}>
     */
    public function findTopSourcesByDateRange(DateRange $range, int $limit = 20): array;

    public function getTableName(): string;
}
