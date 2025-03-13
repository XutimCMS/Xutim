<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;

trait FileTrait
{
    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @return Collection<int, File>
     */
    public function getImages(): Collection
    {
        return $this->files->filter(fn (File $file) => $file->isImage());
    }

    public function addFile(File $file): void
    {
        $this->files->add($file);
    }

    public function removeFile(File $file): void
    {
        $this->files->removeElement($file);
    }

    public function getImage(): ?File
    {
        foreach ($this->files as $file) {
            if ($file->isImage() === true) {
                return $file;
            }
        }

        return null;
    }
}
