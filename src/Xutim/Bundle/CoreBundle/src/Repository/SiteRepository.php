<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\SiteInterface;
use Xutim\CoreBundle\Exception\LogicException;

/**
 * @extends ServiceEntityRepository<SiteInterface>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findDefaultSite(): SiteInterface
    {
        $site = $this->findOneBy([]);

        if ($site === null) {
            throw new LogicException('There is no default site available.');
        }

        return $site;
    }

    public function save(SiteInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiteInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
