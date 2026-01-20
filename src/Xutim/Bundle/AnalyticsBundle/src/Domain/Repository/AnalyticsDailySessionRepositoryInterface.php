<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailySessionInterface;

interface AnalyticsDailySessionRepositoryInterface
{
    public function save(AnalyticsDailySessionInterface $entity, bool $flush = false): void;

    public function deleteByDate(\DateTimeImmutable $date): int;

    /**
     * @param list<array{entryPath: string, exitPath: string, sessionCount: int, totalPageviews: int, bounces: int, totalDurationSeconds: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int;

    public function getTotalSessions(DateRange $range): int;

    public function getTotalBounces(DateRange $range): int;

    public function getBounceRate(DateRange $range): float;

    public function getAverageSessionDuration(DateRange $range): float;

    public function getAveragePagesPerSession(DateRange $range): float;

    /**
     * @return list<array{entryPath: string, sessions: int}>
     */
    public function findTopEntryPages(DateRange $range, int $limit = 10): array;

    /**
     * @return list<array{exitPath: string, sessions: int}>
     */
    public function findTopExitPages(DateRange $range, int $limit = 10): array;

    public function getBounceRateForEntryPage(string $path, DateRange $range): float;

    public function getTableName(): string;
}
