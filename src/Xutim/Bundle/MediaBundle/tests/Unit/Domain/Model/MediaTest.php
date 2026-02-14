<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Model\Media;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaTranslation;
use Xutim\MediaBundle\Domain\Model\MediaTranslationInterface;

final class MediaTest extends TestCase
{
    public function testConstructor(): void
    {
        $folder = $this->createStub(MediaFolderInterface::class);

        $media = new Media(
            folder: $folder,
            originalPath: '/uploads/photo.jpg',
            originalExt: 'JPG',
            mime: 'IMAGE/JPEG',
            hash: 'abc123',
            sizeBytes: 1024,
            width: 1920,
            height: 1080,
            copyright: 'Test Author',
            focalX: 0.5,
            focalY: 0.3,
            blurHash: 'LEHV6nWB2yk8',
        );

        $this->assertSame($folder, $media->folder());
        $this->assertSame('uploads/photo.jpg', $media->originalPath());
        $this->assertSame('jpg', $media->originalExt());
        $this->assertSame('image/jpeg', $media->mime());
        $this->assertSame('abc123', $media->hash());
        $this->assertSame(1024, $media->sizeBytes());
        $this->assertSame(1920, $media->width());
        $this->assertSame(1080, $media->height());
        $this->assertSame('Test Author', $media->copyright());
        $this->assertSame(0.5, $media->focalX());
        $this->assertSame(0.3, $media->focalY());
        $this->assertSame('LEHV6nWB2yk8', $media->blurHash());
        $this->assertNotNull($media->id());
        $this->assertNotNull($media->getCreatedAt());
        $this->assertNotNull($media->getUpdatedAt());
    }

    public function testConstructorNormalizesPath(): void
    {
        $media = new Media(
            folder: null,
            originalPath: '/leading/slash.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $this->assertSame('leading/slash.jpg', $media->originalPath());
    }

    public function testConstructorLowercasesExtAndMime(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.PNG',
            originalExt: 'PNG',
            mime: 'IMAGE/PNG',
            hash: 'h',
            sizeBytes: 100,
        );

        $this->assertSame('png', $media->originalExt());
        $this->assertSame('image/png', $media->mime());
    }

    public function testConstructorClampsNegativeDimensions(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
            width: -10,
            height: -20,
        );

        $this->assertSame(0, $media->width());
        $this->assertSame(0, $media->height());
    }

    public function testIsImageForImageMime(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $this->assertTrue($media->isImage());
    }

    public function testIsImageForNonImageMime(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.pdf',
            originalExt: 'pdf',
            mime: 'application/pdf',
            hash: 'h',
            sizeBytes: 100,
        );

        $this->assertFalse($media->isImage());
    }

    public function testChangeSkipsNullValues(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
            copyright: 'Original',
            focalX: 0.1,
            focalY: 0.2,
            blurHash: 'hash1',
        );

        $media->change();

        $this->assertSame('Original', $media->copyright());
        $this->assertSame(0.1, $media->focalX());
        $this->assertSame(0.2, $media->focalY());
        $this->assertSame('hash1', $media->blurHash());
    }

    public function testChangeUpdatesProvidedValues(): void
    {
        $folder = $this->createStub(MediaFolderInterface::class);
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $media->change(
            copyright: 'New Author',
            focalX: 0.7,
            focalY: 0.8,
            folder: $folder,
            blurHash: 'newHash',
        );

        $this->assertSame('New Author', $media->copyright());
        $this->assertSame(0.7, $media->focalX());
        $this->assertSame(0.8, $media->focalY());
        $this->assertSame($folder, $media->folder());
        $this->assertSame('newHash', $media->blurHash());
    }

    public function testChangeFolder(): void
    {
        $folder = $this->createStub(MediaFolderInterface::class);
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $media->changeFolder($folder);

        $this->assertSame($folder, $media->folder());
    }

    public function testChangeCopyright(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $media->changeCopyright('New Copyright');

        $this->assertSame('New Copyright', $media->copyright());
    }

    public function testChangeBlurHash(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $media->changeBlurHash('LEHV6nWB');

        $this->assertSame('LEHV6nWB', $media->blurHash());
    }

    public function testTranslations(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $this->assertCount(0, $media->getTranslations());
        $this->assertNull($media->getTranslationByLocale('en'));

        $translation = new MediaTranslation($media, 'en', 'Photo', 'A nice photo');
        $media->addTranslation($translation);

        $this->assertCount(1, $media->getTranslations());
        $this->assertSame($translation, $media->getTranslationByLocale('en'));
        $this->assertNull($media->getTranslationByLocale('de'));
    }

    public function testAddTranslationDoesNotDuplicate(): void
    {
        $media = new Media(
            folder: null,
            originalPath: 'test.jpg',
            originalExt: 'jpg',
            mime: 'image/jpeg',
            hash: 'h',
            sizeBytes: 100,
        );

        $translation = $this->createStub(MediaTranslationInterface::class);
        $media->addTranslation($translation);
        $media->addTranslation($translation);

        $this->assertCount(1, $media->getTranslations());
    }
}
