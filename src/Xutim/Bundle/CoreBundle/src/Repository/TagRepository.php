<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;

/**
 * @extends ServiceEntityRepository<TagInterface>
 */
class TagRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'tag.id',
        'name' => 'translation.name',
        'slug' => 'translation.slug',
        'updatedAt' => 'translation.updatedAt',
        'publicationStatus' => 'tag.status',
    ];

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly string $defaultLocale,
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function save(TagInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TagInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function queryByFilter(FilterDto $filter, ?string $locale = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('tag')
            ->select('tag')
            ->leftJoin('tag.translations', 'translation', 'WITH', 'translation.locale = :localeParam')
            ->leftJoin('tag.translations', 'fallbackTranslation', 'WITH', 'fallbackTranslation.locale = :fallbackLocale')
            ->setParameter('localeParam', $locale)
            ->setParameter('fallbackLocale', $this->defaultLocale);

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.name WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.name ELSE translation.name END)',
                        ':searchTerm'
                    ),
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.slug WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.slug ELSE translation.slug END)',
                        ':searchTerm'
                    ),
                ))
                ->setParameter('searchTerm', '%' . strtolower($filter->searchTerm) . '%');
        }

        $hasOrder = in_array($filter->orderColumn, array_keys(self::FILTER_ORDER_COLUMN_MAP), true);

        if ($filter->orderColumn === 'updatedAt') {
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE translation.updatedAt
                     END',
                    $filter->getOrderDir()
                );
        } elseif ($hasOrder) {
            $builder->orderBy(
                self::FILTER_ORDER_COLUMN_MAP[$filter->orderColumn],
                $filter->getOrderDir()
            );
        } else {
            $builder->orderBy('tag.updatedAt', 'desc');
        }

        if ($filter->hasCol('publicationStatus')) {
            /** @var string $status */
            $status = $filter->col('publicationStatus');
            $builder
                ->andWhere('tag.status = :colStatus')
                ->setParameter('colStatus', $status);
        }

        if ($filter->hasCol('translationStatus')) {
            /** @var string $translationStatus */
            $translationStatus = $filter->col('translationStatus');

            if ($translationStatus === 'translated') {
                $builder->andWhere('translation.id IS NOT NULL');
            } elseif ($translationStatus === 'missing') {
                $builder->andWhere('translation.id IS NULL');
            }
        }

        if ($filter->hasCol('name')) {
            /** @var string $name */
            $name = $filter->col('name');
            $builder
                ->andWhere(
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.name WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.name ELSE translation.name END)',
                        ':colName'
                    )
                )
                ->setParameter('colName', sprintf('%%%s%%', strtolower($name)));
        }

        if ($filter->hasCol('excludeFromNews')) {
            $excludeFromNews = $filter->col('excludeFromNews') === 'true';
            $builder
                ->andWhere('tag.excludeFromNews = :colExcludeFromNews')
                ->setParameter('colExcludeFromNews', $excludeFromNews);
        }

        if ($filter->hasCol('updatedAt')) {
            /** @var string $updatedAtRange */
            $updatedAtRange = $filter->col('updatedAt');
            $now = new \DateTimeImmutable();

            if (in_array($updatedAtRange, ['7', '30', '90'], true)) {
                $since = $now->modify('-' . $updatedAtRange . ' days');
                $builder
                    ->andWhere(
                        'CASE
                            WHEN translation.id IS NOT NULL THEN translation.updatedAt
                            WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                            ELSE translation.updatedAt
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
                            ELSE translation.updatedAt
                         END < :updatedBefore'
                    )
                    ->setParameter('updatedBefore', $since);
            }
        }

        return $builder;
    }

    /**
     * @param array<string> $locales
     */
    public function countTranslatedTranslations(TagInterface $tag, ?array $locales): int
    {
        $builder = $this->createQueryBuilder('tag')
            ->select('COUNT(trans.id)')
            ->leftJoin('tag.translations', 'trans')
            ->where('tag = :tagParam')
            ->setParameter('tagParam', $tag);
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

    /**
     * @param list<string> $locales
     */
    public function countUntranslatedForLocales(array $locales): int
    {
        if ($locales === []) {
            return 0;
        }

        $qb = $this->createQueryBuilder('tag');
        $qb
            ->select('tag.id')
            ->leftJoin('tag.translations', 'translation', 'WITH', $qb->expr()->in('translation.locale', ':locales'))
            ->where('tag.status = :status')
            ->groupBy('tag.id')
            ->having(
                $qb->expr()->orX(
                    $qb->expr()->eq($qb->expr()->count('translation.id'), 0),
                    $qb->expr()->lt($qb->expr()->countDistinct('translation.locale'), ':localeCount')
                )
            )
            ->setParameter('locales', $locales)
            ->setParameter('localeCount', count($locales))
            ->setParameter('status', PublicationStatus::Published);

        return count($qb->getQuery()->getResult());
    }

    /**
     * @return array<TagInterface>
     */
    public function findAllPublished(): array
    {
        /** @var array<TagInterface> */
        return $this->createQueryBuilder('tag')
            ->where('tag.status = :status')
            ->setParameter('status', PublicationStatus::Published)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<TagInterface>
     */
    public function findAllSorted(string $locale = 'en'): array
    {
        /** @var array<TagInterface> */
        return $this->createQueryBuilder('tag')
            ->select('tag', 'translation')
            ->leftJoin('tag.translations', 'translation')
            ->where('translation.locale = :localeParam')
            ->setParameter('localeParam', $locale)
            ->orderBy('LOWER(translation.name)', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
