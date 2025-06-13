<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto;

final readonly class SiteDto
{
    /**
     * @param array<string> $locales
     * @param array<string> $extendedContentLocales
     */
    public function __construct(
        public array $locales,
        public array $extendedContentLocales,
        public string $theme,
        public string $sender
    ) {
    }
}
