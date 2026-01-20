<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventArchiveInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventArchiveRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsEventArchiveInterface>
 */
class AnalyticsEventArchiveRepository extends ServiceEntityRepository implements AnalyticsEventArchiveRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(AnalyticsEventArchiveInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }

    public function countAll(): int
    {
        /** @var int|string|null $result */
        $result = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function insertFromEventTable(string $eventTable, \DateTimeImmutable $cutoffDate, int $limit): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $archiveTableQuoted = $conn->quoteSingleIdentifier($this->getTableName());
        $eventTableQuoted = $conn->quoteSingleIdentifier($eventTable);

        $sql = <<<SQL
            INSERT INTO {$archiveTableQuoted}
            SELECT * FROM {$eventTableQuoted}
            WHERE recorded_at < :cutoff
            ORDER BY recorded_at ASC
            LIMIT :limit
        SQL;

        /** @var int */
        return $conn->executeStatement($sql, [
            'cutoff' => $cutoffDate->format('Y-m-d H:i:s'),
            'limit' => $limit,
        ]);
    }
}
