<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

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

    /**
     * @return array<BlockInterface>
    */
    public function findByArticle(ArticleInterface $article): array
    {
        /** @var array<BlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('block')
            ->leftJoin('block.blockItems', 'item')
            ->leftJoin('item.article', 'article')
            ->where('article = :articleParam')
            ->setParameter('articleParam', $article)
            ->getQuery()
            ->getResult()
        ;

        return $blocks;
    }

    /**
     * @return array<BlockInterface>
    */
    public function findByPage(PageInterface $page): array
    {
        /** @var array<BlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('block')
            ->leftJoin('block.blockItems', 'item')
            ->leftJoin('item.page', 'page')
            ->where('page = :pageParam')
            ->setParameter('pageParam', $page)
            ->getQuery()
            ->getResult()
        ;

        return $blocks;
    }

    /**
     * @return array<BlockInterface>
    */
    public function findByTag(TagInterface $tag): array
    {
        /** @var array<BlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('block')
            ->leftJoin('block.blockItems', 'item')
            ->leftJoin('item.tag', 'tag')
            ->where('tag = :tagParam')
            ->setParameter('tagParam', $tag)
            ->getQuery()
            ->getResult()
        ;

        return $blocks;
    }

    /**
     * @return array<BlockInterface>
    */
    public function findBySnippet(SnippetInterface $snippet): array
    {
        /** @var array<BlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('block')
            ->leftJoin('block.blockItems', 'item')
            ->leftJoin('item.snippet', 'snippet')
            ->where('snippet = :snippetParam')
            ->setParameter('snippetParam', $snippet)
            ->getQuery()
            ->getResult()
        ;

        return $blocks;
    }

    /**
     * @return array<BlockInterface>
    */
    public function findByFile(FileInterface $file): array
    {
        /** @var array<BlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('block')
            ->leftJoin('block.blockItems', 'item')
            ->leftJoin('item.file', 'file')
            ->where('file = :fileParam')
            ->setParameter('fileParam', $file)
            ->getQuery()
            ->getResult()
        ;

        return $blocks;
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
