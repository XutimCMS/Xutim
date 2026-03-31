<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class InlineRun
{
    /**
     * @param list<TextMark> $marks
     */
    public function __construct(
        public string $text,
        public array $marks = [],
    ) {
    }
}
