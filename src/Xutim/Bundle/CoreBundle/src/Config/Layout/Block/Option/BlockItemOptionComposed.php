<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

class BlockItemOptionComposed implements BlockItemOption
{
    /** @var array<BlockItemOption> */
    private readonly array $options;

    public function __construct(BlockItemOption ...$options)
    {
        $this->options = $options;
    }

    public function canFullFill(BlockItemInterface $item): bool
    {
        foreach ($this->options as $option) {
            if ($option->canFullFill($item) === false) {
                return false;
            }
        }

        return true;
    }
}
