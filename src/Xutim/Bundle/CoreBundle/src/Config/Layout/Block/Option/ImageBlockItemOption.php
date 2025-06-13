<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

class ImageBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        $file = $item->getFile();
        return $file !== null && $file->isImage() === true;
    }
}
