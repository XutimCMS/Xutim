<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;
use Xutim\MediaBundle\Repository\MediaTranslationRepositoryInterface;

/**
 * @extends ServiceEntityRepository<MediaTranslationInterface>
 */
class MediaTranslationRepository extends ServiceEntityRepository implements MediaTranslationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function removeByMedia(MediaInterface $media): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.media = :media')
            ->setParameter('media', $media)
            ->getQuery()
            ->execute();
    }

    public function save(MediaTranslationInterface $translation, bool $flush = false): void
    {
        $this->getEntityManager()->persist($translation);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
