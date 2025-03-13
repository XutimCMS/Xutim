<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Entity\MenuItem;

/**
 * @extends ServiceEntityRepository<MenuItem>
 */
class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /**
     * @return array<MenuItem>
     */
    public function findByHierarchy(): array
    {
        $builder = $this->createQueryBuilder('node');

        return $builder->leftJoin('node.children', 'children')
                ->addSelect('children')
                ->leftJoin('node.page', 'page')
                ->leftJoin('page.translations', 'pageTrans')
                ->leftJoin('node.article', 'article')
                ->leftJoin('article.translations', 'articleTrans')
                ->orderBy('node.parent, node.position')
                ->getQuery()
                ->getResult();
    }

    /**
     * @return array<MenuItem>
     */
    public function getPathHydrated(MenuItem $item): array
    {
        $path = [];
        $current = $item;

        while ($current) {
            $path[] = $current;
            $current = $current->getParent();
        }

        return array_reverse($path);
    }

    public function moveUp(MenuItem $item, int $step = 1): void
    {
        $item->movePosUp($step);
    }

    public function moveDown(MenuItem $item, int $step = 1): void
    {
        $item->movePosDown($step);
    }



    public function save(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
