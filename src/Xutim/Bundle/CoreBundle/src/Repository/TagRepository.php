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
        'slug' => 'translation.slug'
    ];

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
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

    public function queryByFilter(FilterDto $filter, string $locale = 'en'): QueryBuilder
    {
        $builder = $this->createQueryBuilder('tag')
            ->select('tag', 'translation')
            ->leftJoin('tag.translations', 'translation');
        // ->where('translation.locale = :localeParam')
        // ->setParameter('localeParam', $locale);
        if ($filter->hasSearchTerm() === true) {
            $builder
                ->andWhere($builder->expr()->orX(
                    $builder->expr()->like('LOWER(translation.name)', ':searchTerm'),
                    $builder->expr()->like('LOWER(translation.slug)', ':searchTerm'),
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
            $builder->orderBy('tag.updatedAt', 'desc');
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
