<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

/**
 * @extends ServiceEntityRepository<BlockInterface>
 */
class BlockRepository extends ServiceEntityRepository
{
    public const array FILTER_ORDER_COLUMN_MAP = [
        'name' => 'block.name',
        'description' => 'block.description',
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findByCode(string $code): ?BlockInterface
    {
        /** @var BlockInterface|null */
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
                    $builder->expr()->like('LOWER(block.name)', ':searchTerm'),
                    $builder->expr()->like('LOWER(block.description)', ':searchTerm')
                ))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
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

    public function save(BlockInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlockInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
