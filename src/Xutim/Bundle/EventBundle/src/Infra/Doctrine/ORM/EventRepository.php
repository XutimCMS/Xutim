<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Infra\Doctrine\ORM;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\EventBundle\Domain\Model\EventInterface;

/**
 * @extends ServiceEntityRepository<EventInterface>
 */
class EventRepository extends ServiceEntityRepository
{
    public const FILTER_ORDER_COLUMN_MAP = [
        'id' => 'event.id',
        'title' => 'translation.title',
        'location' => 'translation.location',
        'startsAt' => 'event.startsAt',
        'endsAt' => 'event.endsAt',
        'updatedAt' => 'translation.updatedAt',
        'publicationStatus' => 'event.status',
    ];

    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly string $defaultLocale,
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function queryByFilter(FilterDto $filter, ?string $locale = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('event')
            ->select('event')
            ->leftJoin('event.translations', 'translation', 'WITH', 'translation.locale = :localeParam')
            ->leftJoin('event.translations', 'fallbackTranslation', 'WITH', 'fallbackTranslation.locale = :fallbackLocale')
            ->setParameter('localeParam', $locale)
            ->setParameter('fallbackLocale', $this->defaultLocale);

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE translation.title END)',
                        ':searchTerm'
                    ),
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.location WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.location ELSE translation.location END)',
                        ':searchTerm'
                    ),
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.description WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.description ELSE translation.description END)',
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
            $builder
                ->addOrderBy(
                    'CASE
                        WHEN translation.id IS NOT NULL THEN translation.updatedAt
                        WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.updatedAt
                        ELSE translation.updatedAt
                     END',
                    'desc'
                );
        }

        $builder
            ->addOrderBy('event.startsAt', 'asc')
            ->addOrderBy('event.id', 'asc');

        if ($filter->hasCol('publicationStatus')) {
            /** @var string $status */
            $status = $filter->col('publicationStatus');
            $builder
                ->andWhere('event.status = :colStatus')
                ->setParameter('colStatus', $status);
        }

        if ($filter->hasCol('translationStatus')) {
            /** @var string $translationStatus */
            $translationStatus = $filter->col('translationStatus');

            if ($translationStatus === 'translated') {
                $builder->andWhere('translation.id IS NOT NULL');
            } elseif ($translationStatus === 'missing') {
                $builder
                    ->andWhere('translation.id IS NULL')
                    ->andWhere('event.endsAt > :translationFilterNow')
                    ->setParameter('translationFilterNow', new DateTimeImmutable());
            }
        }

        if ($filter->hasCol('title')) {
            /** @var string $title */
            $title = $filter->col('title');
            $builder
                ->andWhere(
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.title WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.title ELSE translation.title END)',
                        ':colTitle'
                    )
                )
                ->setParameter('colTitle', sprintf('%%%s%%', strtolower($title)));
        }

        if ($filter->hasCol('location')) {
            /** @var string $location */
            $location = $filter->col('location');
            $builder
                ->andWhere(
                    $builder->expr()->like(
                        'LOWER(CASE WHEN translation.id IS NOT NULL THEN translation.location WHEN :localeParam != :fallbackLocale AND fallbackTranslation.id IS NOT NULL THEN fallbackTranslation.location ELSE translation.location END)',
                        ':colLocation'
                    )
                )
                ->setParameter('colLocation', sprintf('%%%s%%', strtolower($location)));
        }

        if ($filter->hasCol('updatedAt')) {
            /** @var string $updatedAtRange */
            $updatedAtRange = $filter->col('updatedAt');
            $now = new DateTimeImmutable();

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

        if ($filter->hasCol('startsAt')) {
            /** @var string $startsAtRange */
            $startsAtRange = $filter->col('startsAt');
            $now = new DateTimeImmutable();

            if (in_array($startsAtRange, ['7', '30', '90'], true)) {
                $since = $now->modify('-' . $startsAtRange . ' days');
                $builder
                    ->andWhere('event.startsAt >= :startsSince')
                    ->setParameter('startsSince', $since);
            } elseif ($startsAtRange === '90+') {
                $since = $now->modify('-90 days');
                $builder
                    ->andWhere('event.startsAt < :startsBefore')
                    ->setParameter('startsBefore', $since);
            } elseif ($startsAtRange === 'upcoming') {
                $builder
                    ->andWhere('event.startsAt >= :startsNow')
                    ->setParameter('startsNow', $now);
            }
        }

        return $builder;
    }

    public function queryPublishedByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->queryByFilter($filter, $locale)
            ->andWhere('event.status = :status')
            ->setParameter('status', PublicationStatus::Published)
        ;

        return $builder;
    }

    public function queryUpcomingPublishedByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->queryByFilter($filter, $locale)
            ->andWhere('event.status = :status')
            ->andWhere('event.endsAt > :now')
            ->setParameter('status', PublicationStatus::Published)
            ->setParameter('now', new DateTimeImmutable())

        ;

        return $builder;
    }

    /**
     * @param list<string> $locales
     */
    public function countUntranslatedForLocales(array $locales): int
    {
        if ($locales === []) {
            return 0;
        }

        $qb = $this->createQueryBuilder('event');
        $qb
            ->select('event.id')
            ->leftJoin('event.translations', 'translation', 'WITH', $qb->expr()->in('translation.locale', ':locales'))
            ->where('event.status = :status')
            ->andWhere('event.endsAt > :now')
            ->groupBy('event.id')
            ->having(
                $qb->expr()->orX(
                    $qb->expr()->eq($qb->expr()->count('translation.id'), 0),
                    $qb->expr()->lt($qb->expr()->countDistinct('translation.locale'), ':localeCount')
                )
            )
            ->setParameter('locales', $locales)
            ->setParameter('localeCount', count($locales))
            ->setParameter('status', PublicationStatus::Published)
            ->setParameter('now', new DateTimeImmutable());

        return count($qb->getQuery()->getResult());
    }

    public function save(EventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EventInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
