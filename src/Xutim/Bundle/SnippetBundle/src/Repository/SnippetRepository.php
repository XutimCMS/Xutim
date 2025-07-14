<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

/**
 * @extends ServiceEntityRepository<SnippetInterface>
 */
class SnippetRepository extends ServiceEntityRepository implements SnippetRepositoryInterface
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'snippet.id',
        'code' => 'snippet.code',
        'category' => 'snippet.category',
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
                    $builder->expr()->like('LOWER(snippet.category)', ':searchTerm'),
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
            $builder->orderBy('snippet.category', 'desc');
            $builder->addOrderBy('snippet.code', 'asc');
        }

        return $builder;
    }

    /**
     * @return array<int, SnippetInterface>
     */
    public function findByCategory(SnippetCategory $category): array
    {
        /** @var array<int, SnippetInterface> */
        return $this->createQueryBuilder('snippet')
            ->where('snippet.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findById(mixed $id): ?SnippetInterface
    {
        /** @var SnippetInterface */
        return $this->findOneBy(['id' => $id]);
    }

    public function findByCode(string $code): ?SnippetInterface
    {
        /** @var SnippetInterface */
        return $this->findOneBy(['code' => $code]);
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
