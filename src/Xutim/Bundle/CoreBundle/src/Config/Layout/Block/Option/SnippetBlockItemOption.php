<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class SnippetBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasSnippet();
    }

    public function getName(): string
    {
        return 'Snippet';
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return 'Reference to a named snippet';
    }
}
