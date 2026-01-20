<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailySessionInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsDailySessionInterface>
 */
class AnalyticsDailySessionRepository extends ServiceEntityRepository implements AnalyticsDailySessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsDailySessionInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteByDate(\DateTimeImmutable $date): int
    {
        /** @var int */
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    /**
     * @param list<array{entryPath: string, exitPath: string, sessionCount: int, totalPageviews: int, bounces: int, totalDurationSeconds: int}> $data
     */
    public function insertAggregatedData(\DateTimeImmutable $date, array $data): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $tableName = $this->getTableName();
        $dateStr = $date->format('Y-m-d');

        $inserted = 0;
        foreach ($data as $row) {
            $conn->insert($tableName, [
                'date' => $dateStr,
                'entry_path' => $row['entryPath'],
                'exit_path' => $row['exitPath'],
                'session_count' => $row['sessionCount'],
                'total_pageviews' => $row['totalPageviews'],
                'bounces' => $row['bounces'],
                'total_duration_seconds' => $row['totalDurationSeconds'],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    public function getTotalSessions(DateRange $range): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.sessionCount)')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function getTotalBounces(DateRange $range): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.bounces)')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function getBounceRate(DateRange $range): float
    {
        $totalSessions = $this->getTotalSessions($range);

        if ($totalSessions === 0) {
            return 0.0;
        }

        $totalBounces = $this->getTotalBounces($range);

        return round(($totalBounces / $totalSessions) * 100, 2);
    }

    public function getAverageSessionDuration(DateRange $range): float
    {
        /** @var array{totalDuration: string|null, totalSessions: string|null}|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.totalDurationSeconds) as totalDuration, SUM(e.sessionCount) as totalSessions')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || (int) $result['totalSessions'] === 0) {
            return 0.0;
        }

        return round((int) $result['totalDuration'] / (int) $result['totalSessions'], 2);
    }

    public function getAveragePagesPerSession(DateRange $range): float
    {
        /** @var array{totalPageviews: string|null, totalSessions: string|null}|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.totalPageviews) as totalPageviews, SUM(e.sessionCount) as totalSessions')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || (int) $result['totalSessions'] === 0) {
            return 0.0;
        }

        return round((int) $result['totalPageviews'] / (int) $result['totalSessions'], 2);
    }

    /**
     * @return list<array{entryPath: string, sessions: int}>
     */
    public function findTopEntryPages(DateRange $range, int $limit = 10): array
    {
        /** @var list<array{entryPath: string, sessions: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.entryPath, SUM(e.sessionCount) as sessions')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.entryPath')
            ->orderBy('sessions', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{exitPath: string, sessions: int}>
     */
    public function findTopExitPages(DateRange $range, int $limit = 10): array
    {
        /** @var list<array{exitPath: string, sessions: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.exitPath, SUM(e.sessionCount) as sessions')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.exitPath')
            ->orderBy('sessions', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getBounceRateForEntryPage(string $path, DateRange $range): float
    {
        /** @var array{totalBounces: string|null, totalSessions: string|null}|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.bounces) as totalBounces, SUM(e.sessionCount) as totalSessions')
            ->where('e.entryPath = :path')
            ->andWhere('e.date BETWEEN :from AND :to')
            ->setParameter('path', $path)
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || (int) $result['totalSessions'] === 0) {
            return 0.0;
        }

        return round((int) $result['totalBounces'] / (int) $result['totalSessions'] * 100, 2);
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }
}
