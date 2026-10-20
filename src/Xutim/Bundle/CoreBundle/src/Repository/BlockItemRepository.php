<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

/**
 * @extends ServiceEntityRepository<BlockItemInterface>
 */
class BlockItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return list<BlockItemInterface>
     */
    public function findBySnippet(SnippetInterface $snippet): array
    {
        return $this->findBy(['snippet' => $snippet]);
    }

    public function save(BlockItemInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlockItemInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
