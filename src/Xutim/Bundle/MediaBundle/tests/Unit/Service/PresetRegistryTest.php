<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Service\PresetRegistry;

final class PresetRegistryTest extends TestCase
{
    public function testConstructFromConfig(): void
    {
        $registry = new PresetRegistry([
            'thumb' => [
                'max_width' => 300,
                'max_height' => 200,
                'fit_mode' => 'cover',
                'quality' => ['avif' => 60, 'webp' => 75, 'jpg' => 80],
                'use_focal_point' => true,
                'formats' => ['avif', 'webp', 'jpg'],
                'responsive_widths' => [320, 640],
            ],
        ]);

        $this->assertTrue($registry->has('thumb'));
        $preset = $registry->get('thumb');
        $this->assertNotNull($preset);
        $this->assertSame('thumb', $preset->name);
        $this->assertSame(300, $preset->maxWidth);
        $this->assertSame(200, $preset->maxHeight);
        $this->assertSame(FitMode::Cover, $preset->fitMode);
    }

    public function testGetReturnsNullForUnknown(): void
    {
        $registry = new PresetRegistry();

        $this->assertNull($registry->get('nonexistent'));
    }

    public function testHas(): void
    {
        $registry = new PresetRegistry();

        $this->assertFalse($registry->has('thumb'));

        $registry->add(new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200));

        $this->assertTrue($registry->has('thumb'));
    }

    public function testAll(): void
    {
        $registry = new PresetRegistry();
        $preset1 = new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200);
        $preset2 = new ImagePreset(name: 'header', maxWidth: 1920, maxHeight: 600);

        $registry->add($preset1);
        $registry->add($preset2);

        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($preset1, $all['thumb']);
        $this->assertSame($preset2, $all['header']);
    }

    public function testNames(): void
    {
        $registry = new PresetRegistry();
        $registry->add(new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200));
        $registry->add(new ImagePreset(name: 'header', maxWidth: 1920, maxHeight: 600));

        $this->assertSame(['thumb', 'header'], $registry->names());
    }

    public function testAddOverwritesExisting(): void
    {
        $registry = new PresetRegistry();
        $registry->add(new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200));
        $registry->add(new ImagePreset(name: 'thumb', maxWidth: 500, maxHeight: 400));

        $this->assertCount(1, $registry->all());
        $preset = $registry->get('thumb');
        $this->assertNotNull($preset);
        $this->assertSame(500, $preset->maxWidth);
    }
}
