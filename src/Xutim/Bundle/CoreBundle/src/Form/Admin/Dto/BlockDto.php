<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Entity\Block;

final readonly class BlockDto
{
    public function __construct(
        public string $code,
        public string $name,
        public string $description,
        public ?string $colorHex,
        public string $layout
    ) {
    }

    public static function fromBlock(Block $block): self
    {
        return new self(
            $block->getCode(),
            $block->getName(),
            $block->getDescription(),
            $block->getColor()->getHex(),
            $block->getLayout()
        );
    }
}
