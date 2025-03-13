<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Entity\BlockItem;

class BlockItemOptionUnion implements BlockItemOption
{
    /** @var array<BlockItemOption> */
    private readonly array $options;

    public function __construct(BlockItemOption ...$options)
    {
        $this->options = $options;
    }

    /**
    * At least one option is enough to fulfill
    */
    public function canFullFill(BlockItem $item): bool
    {
        foreach ($this->options as $option) {
            if ($option->canFullFill($item) === true) {
                return true;
            }
        }

        return false;
    }
}
