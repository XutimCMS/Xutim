<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Xutim\CoreBundle\Domain\Model\FileInterface;

trait FileTrait
{
    /**
     * @return Collection<int, FileInterface>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @return Collection<int, FileInterface>
     */
    public function getImages(): Collection
    {
        return $this->files->filter(fn (FileInterface $file) => $file->isImage());
    }

    public function addFile(FileInterface $file): void
    {
        $this->files->add($file);
    }

    public function removeFile(FileInterface $file): void
    {
        $this->files->removeElement($file);
    }

    public function getImage(): ?FileInterface
    {
        foreach ($this->files as $file) {
            if ($file->isImage() === true) {
                return $file;
            }
        }

        return null;
    }
}
