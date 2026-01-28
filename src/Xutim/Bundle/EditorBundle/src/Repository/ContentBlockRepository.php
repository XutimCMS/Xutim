<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;

/**
 * @extends ServiceEntityRepository<ContentBlockInterface>
 */
class ContentBlockRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
    ) {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return list<ContentBlockInterface>
     */
    public function findByDraft(ContentDraftInterface $draft): array
    {
        /** @var list<ContentBlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('b')
            ->where('b.draft = :draft')
            ->andWhere('b.parent IS NULL')
            ->orderBy('b.position', 'ASC')
            ->setParameter('draft', $draft)
            ->getQuery()
            ->getResult();

        return $blocks;
    }

    /**
     * @return list<ContentBlockInterface>
     */
    public function findByParent(ContentBlockInterface $parent): array
    {
        /** @var list<ContentBlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('b')
            ->where('b.parent = :parent')
            ->orderBy('b.slot', 'ASC')
            ->addOrderBy('b.position', 'ASC')
            ->setParameter('parent', $parent)
            ->getQuery()
            ->getResult();

        return $blocks;
    }

    /**
     * @return list<ContentBlockInterface>
     */
    public function findByParentAndSlot(ContentBlockInterface $parent, int $slot): array
    {
        /** @var list<ContentBlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('b')
            ->where('b.parent = :parent')
            ->andWhere('b.slot = :slot')
            ->orderBy('b.position', 'ASC')
            ->setParameter('parent', $parent)
            ->setParameter('slot', $slot)
            ->getQuery()
            ->getResult();

        return $blocks;
    }

    public function getMaxPosition(ContentDraftInterface $draft, ?ContentBlockInterface $parent = null): int
    {
        $qb = $this->createQueryBuilder('b')
            ->select('MAX(b.position)')
            ->where('b.draft = :draft')
            ->setParameter('draft', $draft);

        if ($parent !== null) {
            $qb->andWhere('b.parent = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('b.parent IS NULL');
        }

        /** @var int|null $result */
        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ?? -1;
    }

    /**
     * @return list<ContentBlockInterface>
     */
    public function findByDraftAndParent(
        ContentDraftInterface $draft,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
    ): array {
        $qb = $this->createQueryBuilder('b')
            ->where('b.draft = :draft')
            ->orderBy('b.position', 'ASC')
            ->setParameter('draft', $draft);

        if ($parent !== null) {
            $qb->andWhere('b.parent = :parent')
                ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('b.parent IS NULL');
        }

        if ($slot !== null) {
            $qb->andWhere('b.slot = :slot')
                ->setParameter('slot', $slot);
        }

        /** @var list<ContentBlockInterface> $blocks */
        $blocks = $qb->getQuery()->getResult();

        return $blocks;
    }

    public function save(ContentBlockInterface $block, bool $flush = false): void
    {
        $this->getEntityManager()->persist($block);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContentBlockInterface $block, bool $flush = false): void
    {
        $this->getEntityManager()->remove($block);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function persist(ContentBlockInterface $block, bool $flush = false): void
    {
        $this->getEntityManager()->persist($block);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Convert a block to a different type using native SQL.
     * This bypasses Doctrine's STI restriction on changing entity types.
     *
     * @return ContentBlockInterface The converted block (fresh from database)
     */
    public function convertType(string $blockId, string $newType): ContentBlockInterface
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $metadata = $em->getClassMetadata($this->getEntityName());
        $tableName = $metadata->getTableName();

        $sql = sprintf('UPDATE %s SET type = :type', $tableName);

        // Set type-specific default values
        if ($newType === 'heading') {
            $sql .= ', level = COALESCE(level, 2)';
        }

        $sql .= ', updated_at = NOW() WHERE id = :id';

        $rowsAffected = $connection->executeStatement($sql, [
            'type' => $newType,
            'id' => $blockId,
        ]);

        if ($rowsAffected === 0) {
            throw new \RuntimeException(sprintf('No block found with ID %s to convert', $blockId));
        }

        // Detach all entities and clear identity map to force fresh load
        $em->clear();

        // Re-fetch using Uuid object
        $uuid = Uuid::fromString($blockId);
        $block = $this->find($uuid);

        if ($block === null) {
            // Debug: check if the record exists in DB
            $row = $connection->fetchAssociative(
                sprintf('SELECT id, type FROM %s WHERE id = :id', $tableName),
                ['id' => $blockId]
            );
            throw new \RuntimeException(sprintf(
                'Failed to fetch converted block %s. DB row: %s',
                $blockId,
                json_encode($row)
            ));
        }

        return $block;
    }
}
