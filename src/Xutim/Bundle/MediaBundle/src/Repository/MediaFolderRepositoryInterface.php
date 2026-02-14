<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Repository;

use Symfony\Component\Uid\Uuid;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;

interface MediaFolderRepositoryInterface
{
    public function findById(Uuid $id): ?MediaFolderInterface;

    /**
     * @return list<MediaFolderInterface>
     */
    public function findAll(): array;

    public function save(MediaFolderInterface $folder, bool $flush = false): void;

    public function remove(MediaFolderInterface $folder, bool $flush = false): void;

    /**
     * @return list<MediaFolderInterface>
     */
    public function findByParent(?MediaFolderInterface $parent): array;
}
