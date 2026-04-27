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
        return 'Media folder';
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return 'Reference to a folder in the media library';
    }
}
