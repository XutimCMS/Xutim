<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyPageReferrerInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsDailyPageReferrerInterface>
 */
class AnalyticsDailyPageReferrerRepository extends ServiceEntityRepository implements AnalyticsDailyPageReferrerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsDailyPageReferrerInterface $entity, bool $flush = false): void
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
     * @param list<array{targetPath: string, referrer: string, isExternal: bool, visits: int, uniqueVisitors: int}> $data
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
                'target_path' => $row['targetPath'],
                'referrer' => $row['referrer'],
                'is_external' => $row['isExternal'] ? 'true' : 'false',
                'visits' => $row['visits'],
                'unique_visitors' => $row['uniqueVisitors'],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return list<array{referrer: string, isExternal: bool, visits: int, uniqueVisitors: int}>
     */
    public function findReferrersForPage(string $targetPath, DateRange $range, int $limit = 20): array
    {
        /** @var list<array{referrer: string, isExternal: bool, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.referrer, e.isExternal, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.targetPath = :targetPath')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->setParameter('targetPath', $targetPath)
            ->groupBy('e.referrer, e.isExternal')
            ->orderBy('visits', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{referrer: string, visits: int, uniqueVisitors: int}>
     */
    public function findTopExternalReferrers(DateRange $range, int $limit = 20): array
    {
        /** @var list<array{referrer: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.referrer, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->andWhere('e.isExternal = true')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.referrer')
            ->orderBy('visits', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }
}
