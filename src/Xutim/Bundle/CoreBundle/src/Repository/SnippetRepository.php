<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

/**
 * @extends ServiceEntityRepository<SnippetInterface>
 */
class SnippetRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'snippet.id',
        'code' => 'snippet.code',
        'content' => 'translation.content'
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function queryByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->createQueryBuilder('snippet')
            ->select('snippet', 'translation')
            ->leftJoin('snippet.translations', 'translation');
        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(translation.content)', ':searchTerm'),
                    $builder->expr()->like('LOWER(snippet.code)', ':searchTerm'),
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
            $builder->orderBy('snippet.code', 'desc');
        }

        return $builder;
    }

    public function findByCode(string $code): SnippetInterface
    {
        /** @var SnippetInterface */
        return $this->createQueryBuilder('snippet')
            ->where('snippet.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function save(SnippetInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SnippetInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
