<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Infra\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

/**
 * @extends ServiceEntityRepository<CmsRedirectInterface>
 */
class CmsRedirectRepository extends ServiceEntityRepository implements RedirectRepositoryInterface
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'redirect.id',
        'source' => 'redirect.source',
        'targetContentTranslation' => 'contentTrans.title',
        'locale' => 'redirect.locale',
        'permanent' => 'redirect.permanent',
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function queryByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->createQueryBuilder('redirect')
            ->leftJoin('redirect.targetContentTranslation', 'contentTrans')
        ;

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(redirect.source)', ':searchTerm'),
                    $builder->expr()->like('LOWER(contentTrans.title)', ':searchTerm'),
                    $builder->expr()->like('LOWER(redirect.locale)', ':searchTerm'),
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
        }
        $builder
            ->addOrderBy('redirect.source', 'asc')
        ;

        return $builder;
    }

    public function save(CmsRedirectInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CmsRedirectInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
