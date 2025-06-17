<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Contract\Block;

final readonly class RenderedBlock
{
    public function __construct(
        public string $html,
        public int $cacheTtl
    ) {
    }
}
