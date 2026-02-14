<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Infra\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;

/**
 * @extends ServiceEntityRepository<MediaVariantInterface>
 */
class MediaVariantRepository extends ServiceEntityRepository implements MediaVariantRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findByMediaPresetFormatWidth(
        MediaInterface $media,
        string $preset,
        string $format,
        int $width,
    ): ?MediaVariantInterface {
        /** @var MediaVariantInterface|null */
        return $this->createQueryBuilder('v')
            ->where('v.media = :media')
            ->andWhere('v.preset = :preset')
            ->andWhere('v.format = :format')
            ->andWhere('v.width = :width')
            ->setParameter('media', $media)
            ->setParameter('preset', $preset)
            ->setParameter('format', $format)
            ->setParameter('width', $width)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByMediaPresetFormat(
        MediaInterface $media,
        string $preset,
        string $format,
    ): array {
        /** @var list<MediaVariantInterface> */
        return $this->createQueryBuilder('v')
            ->where('v.media = :media')
            ->andWhere('v.preset = :preset')
            ->andWhere('v.format = :format')
            ->orderBy('v.width', 'ASC')
            ->setParameter('media', $media)
            ->setParameter('preset', $preset)
            ->setParameter('format', $format)
            ->getQuery()
            ->getResult();
    }

    public function findByMediaPreset(
        MediaInterface $media,
        string $preset,
    ): array {
        /** @var list<MediaVariantInterface> */
        return $this->createQueryBuilder('v')
            ->where('v.media = :media')
            ->andWhere('v.preset = :preset')
            ->orderBy('v.width', 'ASC')
            ->addOrderBy('v.format', 'ASC')
            ->setParameter('media', $media)
            ->setParameter('preset', $preset)
            ->getQuery()
            ->getResult();
    }

    public function findAllPaths(): array
    {
        /** @var list<array{path: string}> $results */
        $results = $this->createQueryBuilder('v')
            ->select('v.path')
            ->getQuery()
            ->getResult();

        return array_column($results, 'path');
    }

    public function deleteByMedia(MediaInterface $media): void
    {
        $this->createQueryBuilder('v')
            ->delete()
            ->where('v.media = :media')
            ->setParameter('media', $media)
            ->getQuery()
            ->execute();
    }

    public function save(MediaVariantInterface $variant, bool $flush = false): void
    {
        $this->getEntityManager()->persist($variant);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
