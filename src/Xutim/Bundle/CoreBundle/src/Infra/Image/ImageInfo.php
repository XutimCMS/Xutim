<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Image;

final readonly class ImageInfo
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly float $decimalRatio,
        public readonly string $ratioLabel,
    ) {
    }

    public static function fromDimensions(int $width, int $height): self
    {
        $decimalRatio = round($width / $height, 2);
        $ratioLabel = self::snapRatio($decimalRatio);

        return new self($width, $height, $decimalRatio, $ratioLabel);
    }

    private static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return $a;
    }

    private static function snapRatio(float $decimal): string
    {
        $knownRatios = [
            '1:1' => 1.00,
            '4:3' => 1.33,
            '3:2' => 1.5,
            '16:10' => 1.6,
            '5:3' => 1.67,
            '16:9' => 1.78,
            '21:9' => 2.33,
        ];

        foreach ($knownRatios as $label => $value) {
            if (abs($decimal - $value) < 0.01) {
                return $label; // exact
            }

            if (abs($decimal - $value) < 0.05) {
                return "~$label"; // close
            }
        }

        // fallback to simplified WxH
        $multiplier = 100;
        $gcd = self::gcd((int) round($decimal * $multiplier), $multiplier);
        $numerator = (int) round($decimal * $multiplier / $gcd);
        $denominator = (int) ($multiplier / $gcd);

        return "~{$numerator}:{$denominator}";
    }
}
