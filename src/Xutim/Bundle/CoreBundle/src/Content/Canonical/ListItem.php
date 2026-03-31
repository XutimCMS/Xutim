<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class ListItem
{
    /**
     * @param list<InlineRun> $body
     * @param list<ListItem>  $children
     */
    public function __construct(
        public array $body = [],
        public array $children = [],
    ) {
    }
}
