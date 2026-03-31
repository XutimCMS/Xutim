<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class TextMark
{
    /**
     * @param array<string, scalar|null> $attrs
     */
    public function __construct(
        public string $type,
        public array $attrs = [],
    ) {
    }
}
