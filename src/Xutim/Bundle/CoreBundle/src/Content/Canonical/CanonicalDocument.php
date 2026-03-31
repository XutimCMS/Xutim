<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class CanonicalDocument
{
    /**
     * @param list<CanonicalBlock>         $blocks
     * @param array<string, string|int|bool|null> $meta
     */
    public function __construct(
        public array $blocks,
        public array $meta = [],
    ) {
    }
}
