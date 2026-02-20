<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;

interface MediaRepositoryInterface
{
    public function findById(Uuid $id): ?MediaInterface;

    public function save(MediaInterface $media, bool $flush = false): void;

    public function remove(MediaInterface $media, bool $flush = false): void;

    /**
     * @return list<MediaInterface>
     */
    public function findAllImages(): array;

    /**
     * @return list<MediaInterface>
     */
    public function findAllNonImages(): array;

    public function countImages(): int;

    public function findByOriginalPath(string $originalPath): ?MediaInterface;

    public function findByHash(string $hash): ?MediaInterface;

    public function queryByFolderAndSearch(?MediaFolderInterface $folder, string $searchTerm): QueryBuilder;
}
