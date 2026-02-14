<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Data;

final readonly class GeneratedVariant
{
    public function __construct(
        public string $preset,
        public string $format,
        public int $width,
        public int $height,
        public string $path,
        public string $fingerprint,
        public int $sizeBytes,
    ) {
    }
}
