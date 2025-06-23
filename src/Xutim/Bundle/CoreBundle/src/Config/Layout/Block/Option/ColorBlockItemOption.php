<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class ColorBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasColor();
    }

    public function getName(): string
    {
        return 'color item';
    }
}
