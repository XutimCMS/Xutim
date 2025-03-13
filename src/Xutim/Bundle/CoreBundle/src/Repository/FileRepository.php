<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\CoreBundle\Entity\File;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * @return array<int, File>
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        $builder = $this->createQueryBuilder('file')
            ->leftJoin('file.translations', 'trans')
        ;
        if (strlen(trim($searchTerm)) > 0) {
            $builder
                ->where('trans.name LIKE :searchParam')
                ->setParameter('searchParam', '%' . $searchTerm . '%')
            ;
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * @return array<array{reference: string, extension: string}>
     */
    public function findAllReferences(): array
    {
        /** @var array<array{reference: string, extension: string}> $fileIds */
        $fileIds = $this->createQueryBuilder('file')
            ->select('file.reference', 'file.extension')
            ->where('file.extension IN (:extensions)')
            ->setParameter('extensions', File::ALLOWED_IMAGE_EXTENSIONS)
            ->getQuery()
            ->getArrayResult()
        ;

        return $fileIds;
    }

    public function save(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
