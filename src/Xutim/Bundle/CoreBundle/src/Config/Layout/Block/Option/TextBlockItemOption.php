<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class TextBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasText();
    }

    public function getName(): string
    {
        return 'text item';
    }
}
