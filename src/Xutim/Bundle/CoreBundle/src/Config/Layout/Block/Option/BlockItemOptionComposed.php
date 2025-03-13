<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Entity\BlockItem;

class BlockItemOptionComposed implements BlockItemOption
{
    /** @var array<BlockItemOption> */
    private readonly array $options;

    public function __construct(BlockItemOption ...$options)
    {
        $this->options = $options;
    }

    public function canFullFill(BlockItem $item): bool
    {
        foreach ($this->options as $option) {
            if ($option->canFullFill($item) === false) {
                return false;
            }
        }

        return true;
    }
}
