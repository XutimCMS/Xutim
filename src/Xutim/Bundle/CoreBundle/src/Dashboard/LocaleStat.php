<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

final readonly class LocaleStat
{
    public function __construct(
        public string $locale,
        public int $count,
        public string $url,
    ) {
    }
}
