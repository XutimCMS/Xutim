<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Service\ImageProcessorInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantGenerator;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class VariantGeneratorTest extends TestCase
{
    public function testSkipsNonImage(): void
    {
        $generator = $this->createGenerator();
        $media = $this->createStub(MediaInterface::class);
        $media->method('isImage')->willReturn(false);

        $result = $generator->generateAllPresets($media);

        $this->assertSame([], $result);
    }

    public function testGenerateForPresetSkipsNonImage(): void
    {
        $generator = $this->createGenerator();
        $media = $this->createStub(MediaInterface::class);
        $media->method('isImage')->willReturn(false);
        $preset = new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200);

        $result = $generator->generateForPreset($media, $preset);

        $this->assertSame([], $result);
    }

    public function testGenerateForPreset(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $storage->method('absolutePath')
            ->willReturnCallback(fn (string $path) => '/public/media/' . $path);

        $processor = $this->createStub(ImageProcessorInterface::class);
        $processor->method('supportsFormat')->willReturn(true);
        $processor->method('process')->willReturn([
            'width' => 320,
            'height' => 240,
            'sizeBytes' => 5000,
        ]);

        $generator = $this->createGenerator($processor, $storage);

        $media = $this->createStub(MediaInterface::class);
        $media->method('isImage')->willReturn(true);
        $media->method('originalPath')->willReturn('media/photo.jpg');
        $media->method('hash')->willReturn('mediahash1234567890');
        $media->method('focalX')->willReturn(0.5);
        $media->method('focalY')->willReturn(0.5);

        $preset = new ImagePreset(
            name: 'thumb',
            maxWidth: 640,
            maxHeight: 480,
            formats: ['webp'],
            responsiveWidths: [320, 640],
        );

        $variants = $generator->generateForPreset($media, $preset);

        $this->assertCount(2, $variants);
        $this->assertSame('thumb', $variants[0]->preset);
        $this->assertSame('webp', $variants[0]->format);
        $this->assertSame(320, $variants[0]->width);
        $this->assertSame(240, $variants[0]->height);
        $this->assertSame(5000, $variants[0]->sizeBytes);
    }

    public function testGenerateForPresetSkipsUnsupportedFormat(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $storage->method('absolutePath')->willReturn('/public/media/photo.jpg');

        $processor = $this->createMock(ImageProcessorInterface::class);
        $processor->method('supportsFormat')->with('avif')->willReturn(false);
        $processor->expects($this->never())->method('process');

        $generator = $this->createGenerator($processor, $storage);

        $media = $this->createStub(MediaInterface::class);
        $media->method('isImage')->willReturn(true);
        $media->method('originalPath')->willReturn('media/photo.jpg');

        $preset = new ImagePreset(
            name: 'thumb',
            maxWidth: 320,
            maxHeight: 240,
            formats: ['avif'],
            responsiveWidths: [320],
        );

        $variants = $generator->generateForPreset($media, $preset);

        $this->assertSame([], $variants);
    }

    private function createGenerator(
        ?ImageProcessorInterface $processor = null,
        ?StorageAdapterInterface $storage = null,
    ): VariantGenerator {
        $storage ??= $this->createStub(StorageAdapterInterface::class);
        $processor ??= $this->createStub(ImageProcessorInterface::class);
        $pathResolver = new VariantPathResolver($storage);
        $presetRegistry = new PresetRegistry();

        return new VariantGenerator($processor, $pathResolver, $storage, $presetRegistry);
    }
}
