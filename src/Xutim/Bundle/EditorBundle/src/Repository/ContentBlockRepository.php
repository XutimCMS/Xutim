<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
}
