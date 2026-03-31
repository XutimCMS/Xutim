<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class GalleryImage
{
    public function __construct(
        public ?string $id = null,
        public ?string $url = null,
        public ?string $thumbnailUrl = null,
    ) {
    }
}
