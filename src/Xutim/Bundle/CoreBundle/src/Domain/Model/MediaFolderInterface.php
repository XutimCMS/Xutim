<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

interface MediaFolderInterface
{
    public function getId(): Uuid;

    public function getName(): string;

    /**
     * @return Collection<int, FileInterface>
     */
    public function getFiles(): Collection;

    public function getParent(): ?MediaFolderInterface;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
