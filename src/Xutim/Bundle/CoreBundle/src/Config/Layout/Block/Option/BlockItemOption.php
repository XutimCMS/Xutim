<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Config\Layout\LayoutConfigItem;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

interface BlockItemOption extends LayoutConfigItem
{
    /**
     * Decides if the given item meets all requirements to pass the
     * option check.
     */
    public function canFullFill(BlockItemInterface $item): bool;
}
