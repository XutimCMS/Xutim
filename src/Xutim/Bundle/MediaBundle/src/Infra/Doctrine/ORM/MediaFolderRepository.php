<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;

/**
 * @extends ServiceEntityRepository<MediaFolderInterface>
 */
class MediaFolderRepository extends ServiceEntityRepository implements MediaFolderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findById(Uuid $id): ?MediaFolderInterface
    {
        /** @var MediaFolderInterface|null */
        return parent::find($id);
    }

    /**
     * @return list<MediaFolderInterface>
     */
    public function findAll(): array
    {
        /** @var list<MediaFolderInterface> */
        return $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<MediaFolderInterface>
     */
    public function findByParent(?MediaFolderInterface $parent): array
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC');

        if ($parent === null) {
            $qb->where('f.parent IS NULL');
        } else {
            $qb->where('f.parent = :parent')
                ->setParameter('parent', $parent);
        }

        /** @var list<MediaFolderInterface> */
        return $qb->getQuery()->getResult();
    }

    public function save(MediaFolderInterface $folder, bool $flush = false): void
    {
        $this->getEntityManager()->persist($folder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaFolderInterface $folder, bool $flush = false): void
    {
        $this->getEntityManager()->remove($folder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
