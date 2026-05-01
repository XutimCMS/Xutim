<?php

declare(strict_types=1);

namespace App\Config\Block\Option;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class EmbedUrlBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        $embedUrl = $item->getExtra()['embedUrl'] ?? null;

        return $embedUrl !== null && $embedUrl !== '';
    }

    public function getName(): string
    {
        return 'embed url item';
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
