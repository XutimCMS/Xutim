<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;
use Xutim\MediaBundle\Domain\Event\MediaCopyrightUpdatedEvent;
use Xutim\MediaBundle\Domain\Event\MediaDeletedEvent;
use Xutim\MediaBundle\Domain\Event\MediaFocalPointUpdatedEvent;
use Xutim\MediaBundle\Domain\Event\MediaFolderCreatedEvent;
use Xutim\MediaBundle\Domain\Event\MediaFolderUpdatedEvent;
use Xutim\MediaBundle\Domain\Event\MediaMovedEvent;
use Xutim\MediaBundle\Domain\Event\MediaTranslationUpdatedEvent;
use Xutim\MediaBundle\Domain\Event\MediaUploadedEvent;
use Xutim\MediaBundle\Domain\Event\VariantsRegeneratedEvent;

final class DomainEventsTest extends TestCase
{
    public function testMediaUploadedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaUploadedEvent($id, 'media/photo.jpg', 'image/jpeg', 2048);

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame('media/photo.jpg', $event->originalPath);
        $this->assertSame('image/jpeg', $event->mime);
        $this->assertSame(2048, $event->sizeBytes);
    }

    public function testMediaDeletedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaDeletedEvent($id, 'media/photo.jpg');

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame('media/photo.jpg', $event->originalPath);
    }

    public function testMediaCopyrightUpdatedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaCopyrightUpdatedEvent($id, 'John Doe');

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame('John Doe', $event->copyright);
    }

    public function testMediaFolderCreatedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaFolderCreatedEvent($id, 'Articles');

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->folderId);
        $this->assertSame('Articles', $event->name);
    }

    public function testMediaFolderUpdatedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaFolderUpdatedEvent($id, 'Updated Name');

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->folderId);
        $this->assertSame('Updated Name', $event->name);
    }

    public function testMediaTranslationUpdatedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaTranslationUpdatedEvent($id, 'en', 'Photo', 'A photo');

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame('en', $event->locale);
        $this->assertSame('Photo', $event->name);
        $this->assertSame('A photo', $event->alt);
    }

    public function testMediaMovedEvent(): void
    {
        $mediaId = Uuid::v4();
        $fromId = Uuid::v4();
        $toId = Uuid::v4();
        $event = new MediaMovedEvent($mediaId, $fromId, $toId);

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($mediaId, $event->mediaId);
        $this->assertSame($fromId, $event->fromFolderId);
        $this->assertSame($toId, $event->toFolderId);
    }

    public function testMediaMovedEventWithNullFolders(): void
    {
        $mediaId = Uuid::v4();
        $event = new MediaMovedEvent($mediaId, null, null);

        $this->assertNull($event->fromFolderId);
        $this->assertNull($event->toFolderId);
    }

    public function testMediaFocalPointUpdatedEvent(): void
    {
        $id = Uuid::v4();
        $event = new MediaFocalPointUpdatedEvent($id, 0.5, 0.3);

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame(0.5, $event->focalX);
        $this->assertSame(0.3, $event->focalY);
    }

    public function testVariantsRegeneratedEvent(): void
    {
        $id = Uuid::v4();
        $event = new VariantsRegeneratedEvent($id, ['thumb', 'header'], 12);

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertSame($id, $event->mediaId);
        $this->assertSame(['thumb', 'header'], $event->presets);
        $this->assertSame(12, $event->variantCount);
    }
}
