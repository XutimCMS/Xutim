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
}
