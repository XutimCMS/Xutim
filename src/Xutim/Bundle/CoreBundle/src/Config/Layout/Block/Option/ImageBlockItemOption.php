<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class ImageBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        $file = $item->getFile();
        return $file !== null && $file->isImage() === true;
    }

    public function getName(): string
    {
        return 'image item';
    }
}
