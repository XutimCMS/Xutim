<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Data;

final readonly class ImagePreset
{
    public const array DEFAULT_RESPONSIVE_WIDTHS = [320, 640, 960, 1280, 1920];
    public const array DEFAULT_QUALITY = ['avif' => 60, 'webp' => 75, 'jpg' => 80];

    /**
     * @param array<string, int> $quality          Quality per format (e.g. ['avif' => 60, 'webp' => 75, 'jpg' => 80])
     * @param list<string>       $formats          Output formats in order of preference (e.g. ['avif', 'webp', 'jpg'])
     * @param list<int>          $responsiveWidths Widths to generate for srcset, filtered to <= maxWidth
     */
    public function __construct(
        public string $name,
        public int $maxWidth,
        public int $maxHeight,
        public FitMode $fitMode = FitMode::Cover,
        public array $quality = self::DEFAULT_QUALITY,
        public bool $useFocalPoint = true,
        public array $formats = ['avif', 'webp', 'jpg'],
        public array $responsiveWidths = self::DEFAULT_RESPONSIVE_WIDTHS,
    ) {
    }

    public function qualityFor(string $format): int
    {
        return $this->quality[$format] ?? $this->quality['jpg'] ?? 80;
    }

    /**
     * Get responsive widths filtered to those <= maxWidth
     *
     * @return list<int>
     */
    public function getEffectiveWidths(): array
    {
        return array_values(array_filter(
            $this->responsiveWidths,
            fn (int $w) => $w <= $this->maxWidth,
        ));
    }

    /**
     * Calculate proportional height for a given width while maintaining aspect ratio
     */
    public function calculateHeight(int $width): int
    {
        if ($this->maxWidth === 0) {
            return $this->maxHeight;
        }

        return (int) round($this->maxHeight * ($width / $this->maxWidth));
    }
}
