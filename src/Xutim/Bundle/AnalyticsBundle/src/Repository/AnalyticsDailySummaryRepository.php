<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailySummaryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsDailySummaryInterface>
 */
class AnalyticsDailySummaryRepository extends ServiceEntityRepository implements AnalyticsDailySummaryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsDailySummaryInterface $entity, bool $flush = false): void
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
     * @param list<array{path: string, pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}> $data
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
                'path' => $row['path'],
                'pageviews' => $row['pageviews'],
                'unique_visitors' => $row['uniqueVisitors'],
                'avg_scroll_depth' => $row['avgScrollDepth'],
                'avg_load_time' => $row['avgLoadTime'],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return list<AnalyticsDailySummaryInterface>
     */
    public function findByDateRange(DateRange $range): array
    {
        /** @var list<AnalyticsDailySummaryInterface> */
        return $this->createQueryBuilder('e')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.pageviews', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{path: string, totalPageviews: int, totalVisitors: int}>
     */
    public function findTopPagesByDateRange(DateRange $range, int $limit = 20): array
    {
        /** @var list<array{path: string, totalPageviews: int, totalVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.path, SUM(e.pageviews) as totalPageviews, SUM(e.uniqueVisitors) as totalVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.path')
            ->orderBy('totalPageviews', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalPageviews(DateRange $range): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.pageviews)')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function getTotalUniqueVisitors(DateRange $range): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.uniqueVisitors)')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function getAverageScrollDepth(DateRange $range): ?float
    {
        /** @var float|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.avgScrollDepth)')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.avgScrollDepth IS NOT NULL')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }

    public function getAverageLoadTime(DateRange $range): ?float
    {
        /** @var float|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.avgLoadTime)')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.avgLoadTime IS NOT NULL')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }

    /**
     * @return list<array{date: string, pageviews: int}>
     */
    public function getPageviewsByDay(DateRange $range): array
    {
        /** @var list<array{date: \DateTimeImmutable, pageviews: string}> $results */
        $results = $this->createQueryBuilder('e')
            ->select('e.date, SUM(e.pageviews) as pageviews')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.date')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (array $row) => [
                'date' => $row['date']->format('Y-m-d'),
                'pageviews' => (int) $row['pageviews'],
            ],
            $results
        );
    }

    /**
     * @return list<array{date: string, pageviews: int}>
     */
    public function getPageviewsByDayForPath(string $path, DateRange $range): array
    {
        /** @var list<array{date: \DateTimeImmutable, pageviews: string}> $results */
        $results = $this->createQueryBuilder('e')
            ->select('e.date, SUM(e.pageviews) as pageviews')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.path = :path')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->setParameter('path', $path)
            ->groupBy('e.date')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (array $row) => [
                'date' => $row['date']->format('Y-m-d'),
                'pageviews' => (int) $row['pageviews'],
            ],
            $results
        );
    }

    /**
     * @return array{pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}|null
     */
    public function getPageStats(string $path, DateRange $range): ?array
    {
        /** @var array{pageviews: string|null, uniqueVisitors: string|null, avgScrollDepth: string|null, avgLoadTime: string|null}|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.pageviews) as pageviews, SUM(e.uniqueVisitors) as uniqueVisitors, AVG(e.avgScrollDepth) as avgScrollDepth, AVG(e.avgLoadTime) as avgLoadTime')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.path = :path')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->setParameter('path', $path)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || $result['pageviews'] === null) {
            return null;
        }

        return [
            'pageviews' => (int) $result['pageviews'],
            'uniqueVisitors' => (int) $result['uniqueVisitors'],
            'avgScrollDepth' => $result['avgScrollDepth'] !== null ? (float) $result['avgScrollDepth'] : null,
            'avgLoadTime' => $result['avgLoadTime'] !== null ? (float) $result['avgLoadTime'] : null,
        ];
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }
}
