<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\Block;

/**
 * @extends ServiceEntityRepository<Block>
 */
class BlockRepository extends ServiceEntityRepository
{
    public const array FILTER_ORDER_COLUMN_MAP = [
        'name' => 'block.name',
        'description' => 'block.description',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    public function findByCode(string $code): ?Block
    {
        /** @var Block|null */
        return $this->createQueryBuilder('block')
            ->where('block.code = :codeParam')
            ->setParameter('codeParam', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function queryByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->createQueryBuilder('block');
        if ($filter->hasSearchTerm() === true) {
            $builder
                ->where($builder->expr()->orX(
                    $builder->expr()->like('block.name', ':searchTerm'),
                    $builder->expr()->like('block.description', ':searchTerm')
                ))
                ->setParameter('searchTerm', '%' . $filter->searchTerm . '%');
        }

        // Check if the order has a valid orderDir and orderColumn parameters.
        if (in_array(
            $filter->orderColumn,
            array_keys(self::FILTER_ORDER_COLUMN_MAP),
            true
        ) === true) {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        } else {
            $builder->orderBy('block.name', 'asc');
        }

        return $builder;
    }

    public function save(Block $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Block $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
