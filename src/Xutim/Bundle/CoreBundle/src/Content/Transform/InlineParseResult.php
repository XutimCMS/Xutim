<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Transform;

use Xutim\CoreBundle\Content\Canonical\InlineRun;

final readonly class InlineParseResult
{
    /**
     * @param list<InlineRun> $runs
     */
    public function __construct(
        public array $runs,
        public bool $hadUnsupportedMarkup = false,
    ) {
    }
}
