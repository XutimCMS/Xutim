<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Domain\Data\ImagePreset;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class VariantPathResolverTest extends TestCase
{
    private function createMediaStub(string $hash = 'abcdef1234567890extra'): MediaInterface
    {
        $media = $this->createStub(MediaInterface::class);
        $media->method('hash')->willReturn($hash);

        return $media;
    }

    public function testBuildPath(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $resolver = new VariantPathResolver($storage);
        $media = $this->createMediaStub('abcdef1234567890extra');
        $preset = new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200);

        $path = $resolver->buildPath($media, $preset, 320, 'webp');

        $this->assertSame('variants/thumb/320/webp/abcdef1234567890.webp', $path);
    }

    public function testGetUrl(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $storage->method('url')
            ->willReturnCallback(fn (string $path) => '/media/' . $path);

        $resolver = new VariantPathResolver($storage);

        $variant = $this->createStub(MediaVariantInterface::class);
        $variant->method('path')->willReturn('variants/thumb/320/webp/abcdef1234567890.webp');
        $variant->method('fingerprint')->willReturn('fingerprint123456');

        $url = $resolver->getUrl($variant);

        $this->assertSame('/media/variants/thumb/320/webp/abcdef1234567890.webp?v=fingerpr', $url);
    }

    public function testCalculateFingerprint(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $resolver = new VariantPathResolver($storage);
        $media = $this->createMediaStub('mediahash');
        $preset = new ImagePreset(
            name: 'thumb',
            maxWidth: 300,
            maxHeight: 200,
            fitMode: FitMode::Cover,
            quality: ['webp' => 75, 'jpg' => 80],
        );

        $fp = $resolver->calculateFingerprint($media, $preset, 320, 'webp');

        $expectedRecipe = 'thumb:300:200:cover:webp:75:320';
        $expected = hash('sha256', 'mediahash:' . $expectedRecipe);
        $this->assertSame($expected, $fp);
    }

    public function testCalculateFingerprintDiffersForDifferentWidths(): void
    {
        $storage = $this->createStub(StorageAdapterInterface::class);
        $resolver = new VariantPathResolver($storage);
        $media = $this->createMediaStub('mediahash');
        $preset = new ImagePreset(name: 'thumb', maxWidth: 300, maxHeight: 200);

        $fp1 = $resolver->calculateFingerprint($media, $preset, 320, 'webp');
        $fp2 = $resolver->calculateFingerprint($media, $preset, 640, 'webp');

        $this->assertNotSame($fp1, $fp2);
    }
}
