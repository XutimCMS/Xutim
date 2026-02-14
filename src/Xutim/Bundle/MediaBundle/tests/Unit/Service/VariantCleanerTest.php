<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class VariantCleanerTest extends TestCase
{
    private function createMediaStub(string $hash = 'abcdef1234567890extra'): MediaInterface
    {
        $media = $this->createStub(MediaInterface::class);
        $media->method('hash')->willReturn($hash);

        return $media;
    }

    public function testCleanForPreset(): void
    {
        $storage = $this->createMock(StorageAdapterInterface::class);
        $storage->method('exists')->willReturn(true);
        // 2 widths x 2 formats = 4 variants
        $storage->expects($this->exactly(4))->method('delete');

        $cleaner = $this->createCleaner($storage);
        $media = $this->createMediaStub();

        $preset = new ImagePreset(
            name: 'thumb',
            maxWidth: 640,
            maxHeight: 480,
            formats: ['webp', 'jpg'],
            responsiveWidths: [320, 640],
        );

        $deleted = $cleaner->cleanForPreset($media, $preset);

        $this->assertSame(4, $deleted);
    }

    public function testCleanForPresetSkipsNonExisting(): void
    {
        $storage = $this->createMock(StorageAdapterInterface::class);
        $storage->method('exists')->willReturn(false);
        $storage->expects($this->never())->method('delete');

        $cleaner = $this->createCleaner($storage);
        $media = $this->createMediaStub();

        $preset = new ImagePreset(
            name: 'thumb',
            maxWidth: 640,
            maxHeight: 480,
            formats: ['webp'],
            responsiveWidths: [320],
        );

        $deleted = $cleaner->cleanForPreset($media, $preset);

        $this->assertSame(0, $deleted);
    }

    public function testCleanForMedia(): void
    {
        $storage = $this->createMock(StorageAdapterInterface::class);
        $storage->method('exists')->willReturn(true);
        // 1 width x 1 format per preset, 2 presets = 2 total
        $storage->expects($this->exactly(2))->method('delete');

        $presetRegistry = new PresetRegistry();
        $presetRegistry->add(new ImagePreset(
            name: 'thumb',
            maxWidth: 320,
            maxHeight: 240,
            formats: ['webp'],
            responsiveWidths: [320],
        ));
        $presetRegistry->add(new ImagePreset(
            name: 'header',
            maxWidth: 640,
            maxHeight: 480,
            formats: ['webp'],
            responsiveWidths: [640],
        ));

        $pathResolver = new VariantPathResolver($storage);
        $cleaner = new VariantCleaner($storage, $pathResolver, $presetRegistry);
        $media = $this->createMediaStub();

        $deleted = $cleaner->cleanForMedia($media);

        $this->assertSame(2, $deleted);
    }

    private function createCleaner(StorageAdapterInterface $storage): VariantCleaner
    {
        $pathResolver = new VariantPathResolver($storage);
        $presetRegistry = new PresetRegistry();

        return new VariantCleaner($storage, $pathResolver, $presetRegistry);
    }
}
