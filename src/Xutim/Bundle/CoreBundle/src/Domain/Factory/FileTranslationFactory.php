<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;

class FileTranslationFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('FileTranslation class "%s" does not exist.', $entityClass));
        }
    }

    public function create(
        string $locale,
        string $name,
        string $alt,
        FileInterface $file
    ): FileTranslationInterface {
        /** @var FileTranslationInterface $item */
        $item = new ($this->entityClass)($locale, $name, $alt, $file);

        return $item;
    }
}
