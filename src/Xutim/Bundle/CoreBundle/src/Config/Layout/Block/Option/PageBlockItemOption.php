<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class PageBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasPage();
    }
}
