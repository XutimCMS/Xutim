<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Xutim\AnalyticsBundle\Domain\Model\AnalyticsEventInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AnalyticsEventInterface>
 */
class AnalyticsEventRepository extends ServiceEntityRepository implements AnalyticsEventRepositoryInterface
{
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
}
