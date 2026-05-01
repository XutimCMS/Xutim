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

    public function getName(): string
    {
        return 'Page';
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return 'Reference to an existing page';
    }
}
