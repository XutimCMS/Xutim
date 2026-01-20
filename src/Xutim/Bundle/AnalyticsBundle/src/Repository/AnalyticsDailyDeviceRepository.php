<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsDailyDeviceInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsDailyDeviceInterface>
 */
class AnalyticsDailyDeviceRepository extends ServiceEntityRepository implements AnalyticsDailyDeviceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsDailyDeviceInterface $entity, bool $flush = false): void
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
     * @param list<array{deviceType: string, browser: string, os: string, visits: int, uniqueVisitors: int}> $data
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
                'device_type' => mb_substr($row['deviceType'], 0, 32),
                'browser' => mb_substr($row['browser'], 0, 64),
                'os' => mb_substr($row['os'], 0, 64),
                'visits' => $row['visits'],
                'unique_visitors' => $row['uniqueVisitors'],
            ]);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * @return list<array{deviceType: string, browser: string, os: string, visits: int, uniqueVisitors: int}>
     */
    public function findByDateRange(DateRange $range): array
    {
        /** @var list<array{deviceType: string, browser: string, os: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.deviceType, e.browser, e.os, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.deviceType, e.browser, e.os')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{deviceType: string, visits: int, uniqueVisitors: int}>
     */
    public function findDeviceTypeBreakdown(DateRange $range): array
    {
        /** @var list<array{deviceType: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.deviceType, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.deviceType')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{browser: string, visits: int, uniqueVisitors: int}>
     */
    public function findBrowserBreakdown(DateRange $range): array
    {
        /** @var list<array{browser: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.browser, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.browser')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{os: string, visits: int, uniqueVisitors: int}>
     */
    public function findOsBreakdown(DateRange $range): array
    {
        /** @var list<array{os: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('e.os, SUM(e.visits) as visits, SUM(e.uniqueVisitors) as uniqueVisitors')
            ->where('e.date BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.os')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }
}
