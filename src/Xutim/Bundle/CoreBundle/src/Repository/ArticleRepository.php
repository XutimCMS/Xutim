<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Dto\Admin\FilteredResultDto;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'article.id',
        'title' => 'translation.title',
        'slug' => 'translation.slug'
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return list<Article>
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('article')
            ->select('article', 'translation')
            ->leftJoin('article.translations', 'translation')
            ->orderBy('article.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTranslatedSumByLocale(string $locale): int
    {
        /** @var int $count */
        $count = $this->createQueryBuilder('article')
            ->select('COUNT(DISTINCT article.id)')
            ->innerJoin('article.translations', 'trans')
            ->where('trans.locale = :localeParam')
            ->setParameter('localeParam', $locale)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function getArticlesCount(): int
    {
        /** @var int $count */
        $count = $this->createQueryBuilder('article')
            ->select('COUNT(article.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    public function queryByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->createQueryBuilder('article')
            ->select('article', 'translation', 'page')
            ->leftJoin('article.page', 'page')
            ->leftJoin('article.translations', 'translation');
        /* ->where('translation.locale = :localeParam') */
        /* ->setParameter('localeParam', $locale); */
        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(translation.title)', ':searchTerm'),
                    $builder->expr()->like('LOWER(translation.slug)', ':searchTerm'),
                    $builder->expr()->like('LOWER(translation.description)', ':searchTerm'),
                    // $builder->expr()->like('LOWER(CAST(translation.content AS TEXT))', ':searchTerm')
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
            $builder->orderBy('article.updatedAt', 'desc');
        }

        return $builder;
    }

    /**
     * @return FilteredResultDto<Article>
     */
    private function createFilteredResult(
        FilterDto $filter,
        QueryBuilder $builder
    ): FilteredResultDto {
        /** @var QueryAdapter<Article> $pager */
        $pager = new QueryAdapter($builder);

        $articles = $pager->getSlice($filter->page, $filter->pageLength);
        $length = $pager->getNbResults();

        return new FilteredResultDto(
            $filter->page,
            $filter->pageLength,
            $length,
            $articles
        );
    }

    /**
     * @return FilteredResultDto<Article>
     */
    public function findByFilter(FilterDto $filter, string $locale = 'en'): FilteredResultDto
    {
        $builder = $this->queryByFilter($filter, $locale);

        return $this->createFilteredResult($filter, $builder);
    }

    /**
     * Finds articles that have translations to an old version of default translation (Default translation
     * has changed after the article was translated to another language).
     *
     * @param array<string> $locales
     * @return array<int, Article>
     */
    public function findByChangedDefaultTranslations(array $locales, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('article');

        return $qb
            ->select('article', 'translation')
            ->join(
                'article.translations',
                'translation',
                'WITH',
                $qb->expr()->in('translation.locale', ':locales')
            )
            ->leftJoin('article.defaultTranslation', 'defaultTranslation')
            ->where($qb->expr()->in('translation.locale', ':locales'))
            ->andWhere('translation.updatedAt < defaultTranslation.updatedAt')
            ->setParameter('locales', $locales)
            ->orderBy('article.createdAt', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string> $locales
     * @return array<int, Article>
     */
    public function findByMissingTranslations(array $locales, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('article');

        return $qb
            ->select('article')
            ->leftJoin(
                'article.translations',
                'translation',
                'WITH',
                $qb->expr()->in('translation.locale', ':locales')
            )
            ->groupBy('article')
            ->having(
                $qb->expr()->orX(
                    $qb->expr()->eq($qb->expr()->count('translation.id'), 0),
                    $qb->expr()->lt(
                        $qb->expr()->countDistinct('translation.locale'),
                        ':localeCount'
                    )
                )
            )
            ->setParameter('locales', $locales)
            ->setParameter('localeCount', count($locales))
            ->orderBy('article.createdAt', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string> $locales
     */
    public function countTranslatedTranslations(Article $article, ?array $locales): int
    {
        $builder = $this->createQueryBuilder('article')
            ->select('COUNT(trans.id)')
            ->leftJoin('article.translations', 'trans')
            ->where('article = :articleParam')
            ->andWhere('trans.status = :status')
            ->setParameter('articleParam', $article)
            ->setParameter('status', PublicationStatus::Published);
        if ($locales !== null) {
            $builder
                ->andWhere('trans.locale in (:locales)')
                ->setParameter('locales', $locales);
        }

        /** @var int $translatedTotal */
        $translatedTotal = $builder
            ->getQuery()
            ->getSingleScalarResult();

        return $translatedTotal;
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
