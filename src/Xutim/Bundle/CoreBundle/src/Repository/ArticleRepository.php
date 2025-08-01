<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends ServiceEntityRepository<ArticleInterface>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'article.id',
        'title' => 'translation.title',
        'slug' => 'translation.slug',
        'tags' => 'tagTranslation.name',
        'updatedAt' => 'translation.updatedAt',
        'publishedAt' => 'translation.publishedAt'
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return list<ArticleInterface>
     */
    public function findAll(): array
    {
        /** @var list<ArticleInterface> $articles */
        $articles = $this->createQueryBuilder('article')
            ->select('article', 'translation')
            ->leftJoin('article.translations', 'translation')
            ->orderBy('article.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $articles;
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



    public function queryByFilter(FilterDto $filter, ?string $locale = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('article')
            ->select('article')
            ->leftJoin('article.translations', 'translation', 'WITH', 'translation.locale = :localeParam')
            ->leftJoin('article.defaultTranslation', 'defaultTranslation')
            ->leftJoin('article.tags', 'tag')
            ->leftJoin('tag.translations', 'tagTranslation')
            ->setParameter('localeParam', $locale);

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(COALESCE(translation.title, defaultTranslation.title))', ':searchTerm'),
                    $builder->expr()->like('LOWER(COALESCE(translation.slug, defaultTranslation.slug))', ':searchTerm'),
                    $builder->expr()->like('LOWER(COALESCE(translation.description, defaultTranslation.description))', ':searchTerm'),
                    $builder->expr()->like('LOWER(tagTranslation.name)', ':searchTerm')
                    // $builder->expr()->like('LOWER(CAST(translation.content AS TEXT))', ':searchTerm')
                ))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
        }

        if ($filter->orderColumn === 'publishedAt' || in_array(
            $filter->orderColumn,
            array_keys(self::FILTER_ORDER_COLUMN_MAP),
            true
        ) === false) {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.status = :scheduledParam AND article.scheduledAt IS NOT NULL THEN article.scheduledAt
                        ELSE COALESCE(translation.publishedAt, defaultTranslation.publishedAt)
                     END',
                    $filter->getOrderDir()
                )
                ->setParameter('scheduledParam', PublicationStatus::Scheduled);
        } else {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        }

        return $builder;
    }

    public function queryPublishedNewsByFilter(FilterDto $filter, string $locale): QueryBuilder
    {
        $subQB1 = $this->createQueryBuilder('a2')
            ->select('1')
            ->innerJoin('a2.tags', 't2')
            ->where('a2.id = article.id')
            ->andWhere('t2.excludeFromNews = true');

        $builder = $this->queryByFilter($filter, $locale);
        $builder
            ->andWhere('translation.status = :status')
            ->setParameter('status', PublicationStatus::Published)
            ->andWhere($builder->expr()->not($builder->expr()->exists($subQB1->getDQL())))
        ;

        return $builder;
    }

    public function queryPublishedByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->queryByFilter($filter, $locale)
            ->andWhere('translation.status = :status')
            ->setParameter('status', PublicationStatus::Published)
        ;

        return $builder;
    }

    public function queryPublishedByTagAndFilter(FilterDto $filter, TagInterface $tag, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->queryPublishedByFilter($filter, $locale)
            ->andWhere('tag = :tagParam')
            ->setParameter('tagParam', $tag)
        ;

        return $builder;
    }

    /**
     * Finds articles that have translations to an old version of default translation (Default translation
     * has changed after the article was translated to another language).
     *
     * @param  array<string>                $locales
     * @return array<int, ArticleInterface>
     */
    public function findByChangedDefaultTranslations(array $locales, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('article');
        /** @var list<ArticleInterface> $articles */
        $articles = $qb
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

        return $articles;
    }

    /**
     * @param  array<string>                $locales
     * @return array<int, ArticleInterface>
     */
    public function findByMissingTranslations(array $locales, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('article');

        /** @var list<ArticleInterface> $articles */
        $articles = $qb
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

        return $articles;
    }

    /**
     * @param array<string> $locales
     */
    public function countTranslatedTranslations(ArticleInterface $article, ?array $locales): int
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

    public function save(ArticleInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArticleInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
