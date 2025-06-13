<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Image;

final readonly class FileInfo
{
    public function __construct(
        public readonly int $size,
        public readonly string $extension
    ) {
    }
}
