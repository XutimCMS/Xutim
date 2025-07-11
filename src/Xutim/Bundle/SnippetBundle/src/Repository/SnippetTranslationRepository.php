<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;

/**
 * @extends ServiceEntityRepository<SnippetTranslationInterface>
 */
class SnippetTranslationRepository extends ServiceEntityRepository implements SnippetTranslationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(SnippetTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SnippetTranslationInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
