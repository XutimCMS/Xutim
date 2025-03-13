<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto;

final readonly class SiteDto
{
    /**
     * @param array<string> $locales
     */
    public function __construct(
        public array $locales,
        public string $theme,
        public string $sender
    ) {
    }
}
