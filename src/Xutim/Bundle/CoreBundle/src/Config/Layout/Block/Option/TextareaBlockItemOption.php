<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class TextareaBlockItemOption implements BlockItemOption, InlineEditableOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return $item->hasText();
    }

    public function getName(): string
    {
        return 'Textarea';
    }

    public function isTranslatable(): bool
    {
        return true;
    }

    public function getDescription(): ?string
    {
        return 'Multi-line text block';
    }
}
