<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Data;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Domain\Data\ImagePreset;

final class ImagePresetTest extends TestCase
{
    public function testDefaults(): void
    {
        $preset = new ImagePreset(name: 'thumb', maxWidth: 800, maxHeight: 600);

        $this->assertSame('thumb', $preset->name);
        $this->assertSame(800, $preset->maxWidth);
        $this->assertSame(600, $preset->maxHeight);
        $this->assertSame(FitMode::Cover, $preset->fitMode);
        $this->assertSame(ImagePreset::DEFAULT_QUALITY, $preset->quality);
        $this->assertTrue($preset->useFocalPoint);
        $this->assertSame(['avif', 'webp', 'jpg'], $preset->formats);
        $this->assertSame(ImagePreset::DEFAULT_RESPONSIVE_WIDTHS, $preset->responsiveWidths);
    }

    public function testQualityForKnownFormat(): void
    {
        $preset = new ImagePreset(
            name: 'test',
            maxWidth: 800,
            maxHeight: 600,
            quality: ['avif' => 50, 'webp' => 70, 'jpg' => 85],
        );

        $this->assertSame(50, $preset->qualityFor('avif'));
        $this->assertSame(70, $preset->qualityFor('webp'));
        $this->assertSame(85, $preset->qualityFor('jpg'));
    }

    public function testQualityForUnknownFormatFallsBackToJpg(): void
    {
        $preset = new ImagePreset(
            name: 'test',
            maxWidth: 800,
            maxHeight: 600,
            quality: ['jpg' => 90],
        );

        $this->assertSame(90, $preset->qualityFor('png'));
    }

    public function testQualityForUnknownFormatFallsBackTo80(): void
    {
        $preset = new ImagePreset(
            name: 'test',
            maxWidth: 800,
            maxHeight: 600,
            quality: ['avif' => 50],
        );

        $this->assertSame(80, $preset->qualityFor('png'));
    }

    public function testGetEffectiveWidthsFiltersAboveMaxWidth(): void
    {
        $preset = new ImagePreset(
            name: 'test',
            maxWidth: 1000,
            maxHeight: 600,
            responsiveWidths: [320, 640, 960, 1280, 1920],
        );

        $this->assertSame([320, 640, 960], $preset->getEffectiveWidths());
    }

    public function testGetEffectiveWidthsIncludesExactMaxWidth(): void
    {
        $preset = new ImagePreset(
            name: 'test',
            maxWidth: 960,
            maxHeight: 600,
            responsiveWidths: [320, 640, 960, 1280],
        );

        $this->assertSame([320, 640, 960], $preset->getEffectiveWidths());
    }

    public function testCalculateHeight(): void
    {
        $preset = new ImagePreset(name: 'test', maxWidth: 1920, maxHeight: 1080);

        $this->assertSame(540, $preset->calculateHeight(960));
        $this->assertSame(180, $preset->calculateHeight(320));
        $this->assertSame(1080, $preset->calculateHeight(1920));
    }

    public function testCalculateHeightZeroMaxWidth(): void
    {
        $preset = new ImagePreset(name: 'test', maxWidth: 0, maxHeight: 600);

        $this->assertSame(600, $preset->calculateHeight(320));
    }
}
