<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyCountryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsDailyCountryInterface>
 */
class AnalyticsDailyCountryRepository extends ServiceEntityRepository implements AnalyticsDailyCountryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsDailyCountryInterface $entity, bool $flush = false): void
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
     * @param list<array{country: string, visits: int, uniqueVisitors: int}> $data
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
                'country' => $row['country'],
                'visits' => $row['visits'],
                'unique_visitors' => $row['uniqueVisitors'],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return list<array{country: string, visits: int, uniqueVisitors: int}>
     */
    public function findTopCountriesByDateRange(DateRange $range, int $limit = 50): array
    {
        /** @var list<array{country: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.country, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.country')
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
