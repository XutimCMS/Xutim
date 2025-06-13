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
        'publishedAt' => 'event.publishedAt'
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function queryByFilter(FilterDto $filter, ?string $locale = null): QueryBuilder
    {
        $builder = $this->createQueryBuilder('event')
            ->select('event', 'translation')
            ->leftJoin('event.translations', 'translation');
        if ($locale !== null) {
            $builder
                ->where('translation.locale = :localeParam')
                ->setParameter('localeParam', $locale);
        }

        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(translation.title)', ':searchTerm'),
                    $builder->expr()->like('LOWER(translation.location)', ':searchTerm'),
                    $builder->expr()->like('LOWER(translation.description)', ':searchTerm'),
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
            ->addOrderBy('event.startsAt', 'asc')
            ->addOrderBy('event.id', 'asc')
        ;

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
