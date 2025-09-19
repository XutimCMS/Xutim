<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class MediaFolderBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasMediaFolder() === true;
    }

    public function getName(): string
    {
        return 'media folder item';
    }
}
