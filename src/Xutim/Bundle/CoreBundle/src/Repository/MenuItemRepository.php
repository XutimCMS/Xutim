<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\MenuItemInterface;

/**
 * @extends ServiceEntityRepository<MenuItemInterface>
 */
class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return array<MenuItemInterface>
     */
    public function findByHierarchy(): array
    {
        $builder = $this->createQueryBuilder('node');

        /** @var array<MenuItemInterface> $items */
        $items = $builder->leftJoin('node.children', 'children')
            ->addSelect('children')
            ->leftJoin('node.page', 'page')
            ->leftJoin('page.translations', 'pageTrans')
            ->leftJoin('node.article', 'article')
            ->leftJoin('article.translations', 'articleTrans')
            ->orderBy('node.parent, node.position')
            ->getQuery()
            ->getResult();

        return $items;
    }

    /**
     * @return array<MenuItemInterface>
     */
    public function getPathHydrated(MenuItemInterface $item): array
    {
        $path = [];
        $current = $item;

        while ($current) {
            $path[] = $current;
            $current = $current->getParent();
        }

        return array_reverse($path);
    }

    public function moveUp(MenuItemInterface $item, int $step = 1): void
    {
        $item->movePosUp($step);
    }

    public function moveDown(MenuItemInterface $item, int $step = 1): void
    {
        $item->movePosDown($step);
    }



    public function save(MenuItemInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuItemInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
