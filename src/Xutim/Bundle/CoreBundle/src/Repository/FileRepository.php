<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\File;

/**
 * @extends ServiceEntityRepository<FileInterface>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function queryByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->createQueryBuilder('file')
            ->distinct()
            ->orderBy('file.createdAt', 'desc')
            ->addOrderBy('file.id', 'asc')
        ;
        
        if ($filter->hasSearchTerm() === true) {
            $builder->innerJoin('file.translations', 'trans');

            $terms = preg_split('/\s+/', trim($filter->searchTerm), -1, PREG_SPLIT_NO_EMPTY);
            if ($terms !== false) {
                foreach ($terms as $idx => $term) {
                    $paramName = sprintf('term%s', $idx);

                    $builder
                        ->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->like('LOWER(trans.name)', ':' . $paramName),
                                $builder->expr()->like('LOWER(trans.alt)', ':' . $paramName)
                            )
                        )
                        ->setParameter($paramName, '%' . strtolower($term) . '%');
                }
            }
        }

        return $builder;
    }

    public function queryImagesByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->queryByFilter($filter);

        return $builder
            ->andWhere($builder->expr()->in('LOWER(file.extension)', ':imageExtensions'))
            ->setParameter('imageExtensions', File::ALLOWED_IMAGE_EXTENSIONS)
        ;
    }

    public function queryNonImagesByFilter(FilterDto $filter): QueryBuilder
    {
        $builder = $this->queryByFilter($filter);

        return $builder
            ->andWhere($builder->expr()->notIn('LOWER(file.extension)', ':imageExtensions'))
            ->setParameter('imageExtensions', File::ALLOWED_IMAGE_EXTENSIONS)
        ;
    }

    /**
     * @return array<int, FileInterface>
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        $builder = $this->createQueryBuilder('file')
            ->distinct()
            ->orderBy('file.createdAt', 'desc')
            ->addOrderBy('file.id', 'asc')
        ;
        if (strlen(trim($searchTerm)) > 0) {
            $builder->innerJoin('file.translations', 'trans');

            $terms = preg_split('/\s+/', trim($searchTerm), -1, PREG_SPLIT_NO_EMPTY);
            if ($terms !== false) {
                foreach ($terms as $idx => $term) {
                    $paramName = sprintf('term%s', $idx);

                    $builder
                        ->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->like('LOWER(trans.name)', ':' . $paramName),
                                $builder->expr()->like('LOWER(trans.alt)', ':' . $paramName)
                            )
                        )
                        ->setParameter($paramName, '%' . strtolower($term) . '%');
                }
            }
        }

        /** @var array<int, FileInterface> $files */
        $files = $builder->getQuery()->getResult();

        return $files;
    }

    /**
     * @return array<int, FileInterface>
     */
    public function findAllImages(): array
    {
        $builder = $this->createQueryBuilder('file');

        /** @var array<int, FileInterface> $files */
        $files = $builder
            ->where('LOWER(file.extension) IN (:ids)')
            ->setParameter('ids', File::ALLOWED_IMAGE_EXTENSIONS)
            ->getQuery()
            ->getResult()
        ;

        return $files;
    }

    /**
     * @return array<array{reference: string, extension: string, id: Uuid, alt: string}>
     */
    public function findAllReferences(): array
    {
        /** @var array<array{reference: string, extension: string, id: Uuid, alt:string}> $fileIds */
        $fileIds = $this->createQueryBuilder('file')
            ->select('file.reference', 'file.extension', 'file.id', 'trans.name', 'trans.alt')
            ->leftJoin('file.translations', 'trans')
            ->where('LOWER(file.extension) IN (:extensions)')
            ->setParameter('extensions', File::ALLOWED_IMAGE_EXTENSIONS)
            ->getQuery()
            ->getArrayResult()
        ;

        return $fileIds;
    }

    public function save(FileInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileInterface $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
