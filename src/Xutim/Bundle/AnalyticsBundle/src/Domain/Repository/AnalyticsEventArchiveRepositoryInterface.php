<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Repository;

use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventArchiveInterface;

interface AnalyticsEventArchiveRepositoryInterface
{
    public function save(AnalyticsEventArchiveInterface $entity, bool $flush = false): void;

    public function getTableName(): string;

    public function countAll(): int;

    public function insertFromEventTable(string $eventTable, \DateTimeImmutable $cutoffDate, int $limit): int;
}
