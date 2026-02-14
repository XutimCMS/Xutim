<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Domain\Model\MediaVariant;

final class MediaVariantTest extends TestCase
{
    public function testConstructor(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $variant = new MediaVariant(
            media: $media,
            preset: 'thumb',
            format: 'WEBP',
            width: 640,
            height: 480,
            path: '/variants/thumb/640/webp/abc123.webp',
            fingerprint: 'fp123',
        );

        $this->assertNotNull($variant->id());
        $this->assertSame($media, $variant->media());
        $this->assertSame('thumb', $variant->preset());
        $this->assertSame('webp', $variant->format());
        $this->assertSame(640, $variant->width());
        $this->assertSame(480, $variant->height());
        $this->assertSame('variants/thumb/640/webp/abc123.webp', $variant->path());
        $this->assertSame('fp123', $variant->fingerprint());
        $this->assertNotNull($variant->createdAt());
    }

    public function testFormatIsLowercased(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $variant = new MediaVariant(
            media: $media,
            preset: 'test',
            format: 'AVIF',
            width: 320,
            height: 240,
            path: 'test.avif',
            fingerprint: 'fp',
        );

        $this->assertSame('avif', $variant->format());
    }

    public function testPathIsTrimmed(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $variant = new MediaVariant(
            media: $media,
            preset: 'test',
            format: 'jpg',
            width: 320,
            height: 240,
            path: '///leading/slashes.jpg',
            fingerprint: 'fp',
        );

        $this->assertSame('leading/slashes.jpg', $variant->path());
    }
}
