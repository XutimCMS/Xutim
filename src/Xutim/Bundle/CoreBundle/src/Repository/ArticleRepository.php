<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
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
        'publishedAt' => 'translation.publishedAt',
        'publicationStatus' => 'translation.status'
    ];

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        public string $tagEntityClass,
        private readonly string $defaultLocale
    ) {
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
            ->leftJoin('article.translations', 'fallbackTranslation', 'WITH', 'fallbackTranslation.locale = :fallbackLocale')
            ->leftJoin('article.defaultTranslation', 'defaultTranslation')
            ->leftJoin('article.tags', 'tag')
            ->leftJoin('tag.translations', 'tagTranslation')
            ->setParameter('localeParam', $locale)
            ->setParameter('fallbackLocale', $this->defaultLocale);

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE defaultTranslation.title END)', ':searchTerm'),
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.slug WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.slug ELSE defaultTranslation.slug END)', ':searchTerm'),
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.description WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.description ELSE defaultTranslation.description END)', ':searchTerm'),
                    $builder->expr()->like('LOWER(tagTranslation.name)', ':searchTerm')
                    // $builder->expr()->like('LOWER(CAST(translation.content AS TEXT))', ':searchTerm')
                ))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
        }

        $hasOrder = in_array($filter->orderColumn, array_keys(self::FILTER_ORDER_COLUMN_MAP), true);

        if ($filter->orderColumn === 'publishedAt') {
            $orderDir = $filter->getOrderDir();
            $isDesc = strtolower($orderDir) === 'desc';

            //  DESC puts NULLs last, ASC puts NULLs first
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.status = :scheduledParam AND article.scheduledAt IS NOT NULL THEN ' . ($isDesc ? '0' : '1') . '
                        WHEN translation.id IS NOT NULL AND translation.publishedAt IS NOT NULL THEN ' . ($isDesc ? '0' : '1') . '
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL AND fallbackTranslation.publishedAt IS NOT NULL THEN ' . ($isDesc ? '0' : '1') . '
                        WHEN defaultTranslation.publishedAt IS NOT NULL THEN ' . ($isDesc ? '0' : '1') . '
                        ELSE ' . ($isDesc ? '1' : '0') . '
                     END',
                    'ASC'
                )
                ->addOrderBy(
                    'CASE
                        WHEN translation.status = :scheduledParam AND article.scheduledAt IS NOT NULL THEN article.scheduledAt
                        WHEN translation.id IS NOT NULL THEN translation.publishedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.publishedAt
                        ELSE defaultTranslation.publishedAt
                     END',
                    $orderDir
                )
                ->setParameter('scheduledParam', PublicationStatus::Scheduled);
        } elseif ($hasOrder === false) {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE defaultTranslation.updatedAt
                     END',
                    'desc'
                );
        } elseif ($filter->orderColumn === 'updatedAt') {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE defaultTranslation.updatedAt
                     END',
                    $filter->getOrderDir()
                );
        } else {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        }

        if ($filter->hasCol('title')) {
            /** @var string $title */
            $title = $filter->col('title');
            $builder
                ->andWhere(
                    $builder->expr()->like('LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE defaultTranslation.title END)', ':colTitle')
                )
                ->setParameter('colTitle', sprintf('%%%s%%', strtolower($title)));
        }

        if ($filter->hasCol('tags')) {
            $builder
                ->andWhere('tag.id = :colTagId')
                ->setParameter('colTagId', $filter->col('tags'));
        }

        if ($filter->hasCol('inNews')) {
            // Does the article have any tags at all?
            $subHasAnyTagDql = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from($this->tagEntityClass, 'tAny')
                ->where('tAny MEMBER OF article.tags')
                ->getDQL();

            // Does the article have any tag that is NOT excluded from news?
            $subHasNonExcludedTagDql = $this->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from($this->tagEntityClass, 'tNE')
                ->where('tNE MEMBER OF article.tags')
                ->andWhere('tNE.excludeFromNews = false')
                ->getDQL();

            $inNews = (bool) $filter->col('inNews');

            if ($inNews === false) {
                // EXCLUDED FROM NEWS -> article has at least one tag AND has no non-excluded tags
                // i.e., all its tags are excluded
                $builder->andWhere(
                    $builder->expr()->andX(
                        $builder->expr()->exists($subHasAnyTagDql),
                        $builder->expr()->not($builder->expr()->exists($subHasNonExcludedTagDql))
                    )
                );
            } else {
                // INCLUDED IN NEWS -> either it has no tags OR it has at least one non-excluded tag
                $builder->andWhere(
                    $builder->expr()->orX(
                        $builder->expr()->not($builder->expr()->exists($subHasAnyTagDql)),
                        $builder->expr()->exists($subHasNonExcludedTagDql)
                    )
                );
            }
        }

        if ($filter->hasCol('publicationStatus')) {
            /** @var string $status */
            $status = $filter->col('publicationStatus');
            $builder
                ->andWhere(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.status
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.status
                        ELSE defaultTranslation.status
                     END = :colStatus'
                )
                ->setParameter('colStatus', $status);
        }

        if ($filter->hasCol('translationStatus')) {
            /** @var string $translationStatus */
            $translationStatus = $filter->col('translationStatus');

            if ($translationStatus === 'translated') {
                // Article has translation in requested locale
                $builder->andWhere('translation.id IS NOT NULL');
            } elseif ($translationStatus === 'missing') {
                // Article has no translation in requested locale AND no fallback
                $builder->andWhere('translation.id IS NULL')
                    ->andWhere(
                        $builder->expr()->orX(
                            ':localeParam = :fallbackLocale',
                            'fallbackTranslation.id IS NULL'
                        )
                    );
            } elseif ($translationStatus === 'fallback') {
                // Article is using fallback (not in requested locale, but fallback or default exists)
                $builder->andWhere('translation.id IS NULL')
                    ->andWhere(
                        $builder->expr()->orX(
                            $builder->expr()->andX(
                                ':localeParam != :fallbackLocale',
                                'fallbackTranslation.id IS NOT NULL'
                            ),
                            'defaultTranslation.id IS NOT NULL'
                        )
                    );
            }
        }

        if ($filter->hasCol('updatedAt')) {
            /** @var string $updatedAtRange */
            $updatedAtRange = $filter->col('updatedAt');
            $now = new \DateTimeImmutable();

            if ($updatedAtRange === '7') {
                $since = $now->modify('-7 days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.updatedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                            ELSE defaultTranslation.updatedAt
                         END >= :updatedSince'
                    )
                    ->setParameter('updatedSince', $since);
            } elseif ($updatedAtRange === '30') {
                $since = $now->modify('-30 days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.updatedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                            ELSE defaultTranslation.updatedAt
                         END >= :updatedSince'
                    )
                    ->setParameter('updatedSince', $since);
            } elseif ($updatedAtRange === '90') {
                $since = $now->modify('-90 days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.updatedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                            ELSE defaultTranslation.updatedAt
                         END >= :updatedSince'
                    )
                    ->setParameter('updatedSince', $since);
            } elseif ($updatedAtRange === '90+') {
                $since = $now->modify('-90 days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.updatedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                            ELSE defaultTranslation.updatedAt
                         END < :updatedBefore'
                    )
                    ->setParameter('updatedBefore', $since);
            }
        }

        if ($filter->hasCol('publishedAt')) {
            /** @var string $publishedAtFilter */
            $publishedAtFilter = $filter->col('publishedAt');
            $now = new \DateTimeImmutable();

            if ($publishedAtFilter === 'recent') {
                // Recently published (last 30 days)
                $since = $now->modify('-30 days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.status
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.status
                            ELSE defaultTranslation.status
                         END = :publishedStatus'
                    )
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.publishedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.publishedAt
                            ELSE defaultTranslation.publishedAt
                         END >= :publishedSince'
                    )
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.publishedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.publishedAt
                            ELSE defaultTranslation.publishedAt
                         END <= :now'
                    )
                    ->setParameter('publishedStatus', PublicationStatus::Published)
                    ->setParameter('publishedSince', $since)
                    ->setParameter('now', $now);
            } elseif ($publishedAtFilter === 'scheduled') {
                // Scheduled for future (status = Scheduled)
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.status
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.status
                            ELSE defaultTranslation.status
                         END = :scheduledStatus'
                    )
                    ->setParameter('scheduledStatus', PublicationStatus::Scheduled);
            } elseif ($publishedAtFilter === 'never') {
                // Never published (status = Draft or publishedAt is null)
                $builder
                    ->andWhere(
                        $builder->expr()->orX(
                            'CASE
                                WHEN translation.id IS NOT NULL THEN translation.status
                                WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.status
                                ELSE defaultTranslation.status
                             END = :draftStatus',
                            $builder->expr()->isNull(
                                'CASE
                                    WHEN translation.id IS NOT NULL THEN translation.publishedAt
                                    WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.publishedAt
                                    ELSE defaultTranslation.publishedAt
                                 END'
                            )
                        )
                    )
                    ->setParameter('draftStatus', PublicationStatus::Draft);
            }
        }

        return $builder;
    }

    public function queryPublishedNewsByFilter(FilterDto $filter, string $locale): QueryBuilder
    {
        // article has any tag
        $subHasAnyTag = $this->createQueryBuilder('a2')
            ->select('1')
            ->innerJoin('a2.tags', 't2')
            ->where('a2.id = article.id');

        // article has at least one NON-excluded tag
        $subHasNonExcludedTag = $this->createQueryBuilder('a3')
            ->select('1')
            ->innerJoin('a3.tags', 't3')
            ->where('a3.id = article.id')
            ->andWhere('t3.excludeFromNews = false');

        $builder = $this->queryByFilter($filter, $locale);
        $builder
            ->andWhere('translation.status = :status')
            ->setParameter('status', PublicationStatus::Published)
            // INCLUDED in news <=> (no tags) OR (has a non-excluded tag)
            ->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->not($builder->expr()->exists($subHasAnyTag->getDQL())),
                    $builder->expr()->exists($subHasNonExcludedTag->getDQL())
                )
            );

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
