<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\BlockInterface;

class BlockFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Block class "%s" does not exist.', $entityClass));
        }
    }

    public function create(
        string $code,
        string $name,
        string $description,
        ?string $colorHex,
        string $layout
    ): BlockInterface {
        /** @var BlockInterface $block */
        $block = new ($this->entityClass)($code, $name, $description, $colorHex, $layout);

        return $block;
    }
}
