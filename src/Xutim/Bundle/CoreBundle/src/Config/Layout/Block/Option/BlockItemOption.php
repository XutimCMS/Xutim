<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Config\Layout\LayoutConfigItem;
use Xutim\CoreBundle\Entity\BlockItem;

interface BlockItemOption extends LayoutConfigItem
{
    /**
     * Decides if the given item meets all requirements to pass the
     * option check.
     */
    public function canFullFill(BlockItem $item): bool;
}
