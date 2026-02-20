<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

/**
 * @extends ServiceEntityRepository<MediaInterface>
 */
class MediaRepository extends ServiceEntityRepository implements MediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findById(Uuid $id): ?MediaInterface
    {
        /** @var MediaInterface|null */
        return parent::find($id);
    }

    public function findAllImages(): array
    {
        /** @var list<MediaInterface> */
        return $this->createQueryBuilder('m')
            ->where('m.mime LIKE :mimePrefix')
            ->setParameter('mimePrefix', 'image/%')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllNonImages(): array
    {
        /** @var list<MediaInterface> */
        return $this->createQueryBuilder('m')
            ->where('m.mime NOT LIKE :mimePrefix')
            ->setParameter('mimePrefix', 'image/%')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByOriginalPath(string $originalPath): ?MediaInterface
    {
        /** @var MediaInterface|null */
        return $this->findOneBy(['originalPath' => $originalPath]);
    }

    public function findByHash(string $hash): ?MediaInterface
    {
        /** @var MediaInterface|null */
        return $this->findOneBy(['hash' => $hash]);
    }

    public function countImages(): int
    {
        /** @var int */
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.mime LIKE :mimePrefix')
            ->setParameter('mimePrefix', 'image/%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function queryByFolderAndSearch(?MediaFolderInterface $folder, string $searchTerm): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC');

        if ($searchTerm !== '') {
            $qb->leftJoin('m.translations', 't')
                ->andWhere('LOWER(t.name) LIKE :search OR LOWER(m.originalPath) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($searchTerm) . '%');
        } elseif ($folder !== null) {
            $qb->andWhere('m.folder = :folder')
                ->setParameter('folder', $folder);
        } else {
            $qb->andWhere('m.folder IS NULL');
        }

        return $qb;
    }

    public function save(MediaInterface $media, bool $flush = false): void
    {
        $this->getEntityManager()->persist($media);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaInterface $media, bool $flush = false): void
    {
        $this->getEntityManager()->remove($media);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
