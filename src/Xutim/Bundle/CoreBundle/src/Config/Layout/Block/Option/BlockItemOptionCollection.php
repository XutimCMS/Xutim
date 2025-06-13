<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

class BlockItemOptionCollection implements BlockItemOption
{
    public function __construct(private readonly BlockItemOption $option)
    {
    }

    public function canFullFill(BlockItemInterface $item): bool
    {
        return $this->option->canFullFill($item);
    }
}
