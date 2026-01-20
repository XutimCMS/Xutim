<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Data\DateRange;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsEventInterface>
 */
class AnalyticsEventRepository extends ServiceEntityRepository implements AnalyticsEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsEventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AnalyticsEventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPageviewCount(DateRange $range, bool $excludeBots = true): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getUniqueVisitorCount(DateRange $range, bool $excludeBots = true): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.sessionBucket)')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getAverageScrollDepth(DateRange $range, bool $excludeBots = true): ?float
    {
        $qb = $this->createQueryBuilder('e')
            ->select('AVG(e.scrollDepth)')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.scrollDepth IS NOT NULL')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }

    public function getAverageLoadTime(DateRange $range, bool $excludeBots = true): ?float
    {
        $qb = $this->createQueryBuilder('e')
            ->select('AVG(e.loadTimeMs)')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.loadTimeMs IS NOT NULL')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }

    /**
     * @return array<int, array{date: string, count: int}>
     */
    public function getPageviewsByDay(DateRange $range, bool $excludeBots = true): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $tableName = $this->getClassMetadata()->getTableName();

        $qb = $conn->createQueryBuilder()
            ->select('DATE(recorded_at) as date', 'COUNT(id) as count')
            ->from($tableName)
            ->where('recorded_at BETWEEN :from AND :to')
            ->setParameter('from', $range->from->format('Y-m-d H:i:s'))
            ->setParameter('to', $range->to->format('Y-m-d H:i:s'))
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        if ($excludeBots) {
            $qb->andWhere('is_bot = false');
        }

        /** @var array<int, array{date: string, count: int}> */
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @return array<int, array{path: string, views: int, uniqueVisitors: int, avgScrollDepth: float|null}>
     */
    public function getTopPages(DateRange $range, int $limit = 20, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.path as path, COUNT(e.id) as views')
            ->addSelect('COUNT(DISTINCT e.sessionBucket) as uniqueVisitors')
            ->addSelect('AVG(e.scrollDepth) as avgScrollDepth')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.path')
            ->orderBy('views', 'DESC')
            ->setMaxResults($limit);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{path: string, views: int, uniqueVisitors: int, avgScrollDepth: float|null}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{referer: string, count: int}>
     */
    public function getExternalReferrers(DateRange $range, int $limit = 20, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.referer as referer, COUNT(e.id) as count')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.referer IS NOT NULL')
            ->andWhere("e.referer != ''")
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.referer')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{referer: string, count: int}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{referer: string, count: int}>
     */
    public function getInternalReferrersForPage(string $path, DateRange $range, int $limit = 20, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.referer as referer, COUNT(e.id) as count')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.path = :path')
            ->andWhere('e.referer IS NOT NULL')
            ->andWhere("e.referer != ''")
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->setParameter('path', $path)
            ->groupBy('e.referer')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{referer: string, count: int}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{country: string, count: int}>
     */
    public function getVisitorsByCountry(DateRange $range, int $limit = 50, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.country as country, COUNT(DISTINCT e.sessionBucket) as count')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.country IS NOT NULL')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.country')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit);

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{country: string, count: int}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns raw user agents grouped, to be processed by UserAgentParser for categorization.
     *
     * @return array<int, array{userAgent: string|null, count: int}>
     */
    public function getUserAgentBreakdown(DateRange $range, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.userAgent as userAgent, COUNT(DISTINCT e.sessionBucket) as count')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->groupBy('e.userAgent')
            ->orderBy('count', 'DESC');

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{userAgent: string|null, count: int}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{path: string, views: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}>
     */
    public function getPageStats(string $path, DateRange $range, bool $excludeBots = true): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.path as path, COUNT(e.id) as views, COUNT(DISTINCT e.sessionBucket) as uniqueVisitors')
            ->addSelect('AVG(e.scrollDepth) as avgScrollDepth, AVG(e.loadTimeMs) as avgLoadTime')
            ->where('e.recordedAt BETWEEN :from AND :to')
            ->andWhere('e.path = :path')
            ->setParameter('from', $range->from)
            ->setParameter('to', $range->to)
            ->setParameter('path', $path)
            ->groupBy('e.path');

        if ($excludeBots) {
            $qb->andWhere('e.isBot = false');
        }

        /** @var array<int, array{path: string, views: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{date: string, count: int}>
     */
    public function getPageviewsByDayForPage(string $path, DateRange $range, bool $excludeBots = true): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $tableName = $this->getClassMetadata()->getTableName();

        $qb = $conn->createQueryBuilder()
            ->select('DATE(recorded_at) as date', 'COUNT(id) as count')
            ->from($tableName)
            ->where('recorded_at BETWEEN :from AND :to')
            ->andWhere('path = :path')
            ->setParameter('from', $range->from->format('Y-m-d H:i:s'))
            ->setParameter('to', $range->to->format('Y-m-d H:i:s'))
            ->setParameter('path', $path)
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        if ($excludeBots) {
            $qb->andWhere('is_bot = false');
        }

        /** @var array<int, array{date: string, count: int}> */
        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }

    public function countEventsForDate(\DateTimeImmutable $date): int
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @return list<array{path: string, pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}>
     */
    public function getDailySummaryAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{path: string, pageviews: int, uniqueVisitors: int, avgScrollDepth: float|null, avgLoadTime: float|null}> */
        return $this->createQueryBuilder('e')
            ->select('e.path as path')
            ->addSelect('COUNT(e.id) as pageviews')
            ->addSelect('COUNT(DISTINCT e.sessionBucket) as uniqueVisitors')
            ->addSelect('AVG(e.scrollDepth) as avgScrollDepth')
            ->addSelect('AVG(e.loadTimeMs) as avgLoadTime')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->groupBy('e.path')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{referer: string|null, sessionBucket: string}>
     */
    public function getEventsForTrafficSourceAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{referer: string|null, sessionBucket: string}> */
        return $this->createQueryBuilder('e')
            ->select('e.referer as referer, e.sessionBucket as sessionBucket')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{country: string, visits: int, uniqueVisitors: int}>
     */
    public function getDailyCountryAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{country: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('COALESCE(e.country, \'XX\') as country')
            ->addSelect('COUNT(e.id) as visits')
            ->addSelect('COUNT(DISTINCT e.sessionBucket) as uniqueVisitors')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->groupBy('e.country')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{userAgent: string|null, sessionBucket: string}>
     */
    public function getEventsForDeviceAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{userAgent: string|null, sessionBucket: string}> */
        return $this->createQueryBuilder('e')
            ->select('e.userAgent as userAgent, e.sessionBucket as sessionBucket')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{utmSource: string, utmMedium: string, utmCampaign: string, visits: int, uniqueVisitors: int}>
     */
    public function getDailyUtmAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{utmSource: string, utmMedium: string, utmCampaign: string, visits: int, uniqueVisitors: int}> */
        return $this->createQueryBuilder('e')
            ->select('COALESCE(e.utmSource, \'(none)\') as utmSource')
            ->addSelect('COALESCE(e.utmMedium, \'(none)\') as utmMedium')
            ->addSelect('COALESCE(e.utmCampaign, \'(none)\') as utmCampaign')
            ->addSelect('COUNT(e.id) as visits')
            ->addSelect('COUNT(DISTINCT e.sessionBucket) as uniqueVisitors')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->groupBy('e.utmSource, e.utmMedium, e.utmCampaign')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{path: string, referer: string, sessionBucket: string}>
     */
    public function getEventsForPageReferrerAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{path: string, referer: string, sessionBucket: string}> */
        return $this->createQueryBuilder('e')
            ->select('e.path as path, e.referer as referer, e.sessionBucket as sessionBucket')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->andWhere('e.referer IS NOT NULL')
            ->andWhere("e.referer != ''")
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{sessionBucket: string, path: string, recordedAt: \DateTimeImmutable}>
     */
    public function getEventsForSessionAggregation(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        /** @var list<array{sessionBucket: string, path: string, recordedAt: \DateTimeImmutable}> */
        return $this->createQueryBuilder('e')
            ->select('e.sessionBucket as sessionBucket, e.path as path, e.recordedAt as recordedAt')
            ->where('e.recordedAt BETWEEN :start AND :end')
            ->andWhere('e.isBot = false')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->orderBy('e.sessionBucket', 'ASC')
            ->addOrderBy('e.recordedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getEarliestEventDate(): ?\DateTimeImmutable
    {
        /** @var string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('MIN(e.recordedAt)')
            ->andWhere('e.isBot = false')
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? new \DateTimeImmutable($result) : null;
    }

    public function getLatestEventDate(): ?\DateTimeImmutable
    {
        /** @var string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('MAX(e.recordedAt)')
            ->andWhere('e.isBot = false')
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? new \DateTimeImmutable($result) : null;
    }

    public function countEventsOlderThan(\DateTimeImmutable $cutoffDate): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.recordedAt < :cutoff')
            ->setParameter('cutoff', $cutoffDate)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function deleteEventsOlderThan(\DateTimeImmutable $cutoffDate, int $limit): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $tableName = $this->getTableName();

        /** @var int */
        return $conn->executeStatement(
            "DELETE FROM {$tableName} WHERE recorded_at < :cutoff ORDER BY recorded_at ASC LIMIT :limit",
            [
                'cutoff' => $cutoffDate->format('Y-m-d H:i:s'),
                'limit' => $limit,
            ]
        );
    }
}
