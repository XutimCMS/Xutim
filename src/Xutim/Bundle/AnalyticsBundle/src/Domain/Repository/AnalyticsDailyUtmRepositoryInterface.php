<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyUtmInterface;

interface AnalyticsDailyUtmRepositoryInterface
{
    public function save(AnalyticsDailyUtmInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{utmSource: string, utmMedium: string, utmCampaign: string, visits: int, uniqueVisitors: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    /**
     * @return list<array{utmSource: string, utmMedium: string, utmCampaign: string, visits: int, uniqueVisitors: int}>
     */
    public function findByDateRange(DateRange $range, int $limit = 50): array;

    public function getTableName(): string;
}
