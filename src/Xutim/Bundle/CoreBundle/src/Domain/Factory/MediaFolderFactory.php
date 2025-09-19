<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\MediaFolderInterface;

class MediaFolderFactory
{
    public function __construct(
        private readonly string $mediaFolderClass
    ) {
        if (!class_exists($mediaFolderClass)) {
            throw new \InvalidArgumentException(sprintf('Media folder class "%s" does not exist.', $mediaFolderClass));
        }
    }

    public function create(
        string $name,
        ?MediaFolderInterface $parent,
    ): MediaFolderInterface {
        /** @var MediaFolderInterface $folder */
        $folder = new ($this->mediaFolderClass)(
            $name,
            $parent,
        );

        return $folder;
    }
}
