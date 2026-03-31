<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Twig;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftUpdatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Event\PublicationStatus\PublicationStatusChangedEvent;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;
use Xutim\CoreBundle\Entity\LogEvent;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Twig\Extension\RevisionExtension;
use Xutim\Domain\DomainEvent;

final class RevisionExtensionTest extends TestCase
{
    private RevisionExtension $extension;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $this->extension = new RevisionExtension($translator);
    }

    public function testIsContentEventReturnsTrueForContentEvents(): void
    {
        $this->assertTrue($this->extension->isContentEvent(
            $this->logEvent(new ContentTranslationCreatedEvent(
                Uuid::v4(), '', 'Title', '', 'slug', [], '', 'en', new DateTimeImmutable(), null, null,
            ))
        ));
        $this->assertTrue($this->extension->isContentEvent(
            $this->logEvent(new ContentTranslationUpdatedEvent(
                Uuid::v4(), '', 'Title', '', 'slug', [], '', 'en', new DateTimeImmutable(),
            ))
        ));
    }

    public function testIsContentEventReturnsFalseForDraftEvents(): void
    {
        $this->assertFalse($this->extension->isContentEvent(
            $this->logEvent($this->draftCreatedEvent())
        ));
        $this->assertFalse($this->extension->isContentEvent(
            $this->logEvent($this->draftUpdatedEvent())
        ));
    }

    public function testIsComparableEventIncludesContentAndDraftEvents(): void
    {
        $this->assertTrue($this->extension->isComparableEvent(
            $this->logEvent(new ContentTranslationCreatedEvent(
                Uuid::v4(), '', 'Title', '', 'slug', [], '', 'en', new DateTimeImmutable(), null, null,
            ))
        ));
        $this->assertTrue($this->extension->isComparableEvent(
            $this->logEvent($this->draftCreatedEvent())
        ));
        $this->assertTrue($this->extension->isComparableEvent(
            $this->logEvent($this->draftUpdatedEvent())
        ));
    }

    public function testIsComparableEventReturnsFalseForStatusAndDiscardEvents(): void
    {
        $this->assertFalse($this->extension->isComparableEvent(
            $this->logEvent(new PublicationStatusChangedEvent(Uuid::v4(), 'Object', PublicationStatus::Published))
        ));
        $this->assertFalse($this->extension->isComparableEvent(
            $this->logEvent(new ContentDraftDiscardedEvent(Uuid::v4(), Uuid::v4()))
        ));
    }

    public function testFilterKeepsContentEventsAlways(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->contentUpdatedEvent(), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
    }

    public function testFilterHidesDraftWhenNextContentEventHasSameAuthor(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->contentUpdatedEvent(), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
        $this->assertContainsOnlyContentEvents($filtered);
    }

    public function testFilterKeepsDraftWhenNextContentEventHasDifferentAuthor(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftCreatedEvent(), 'bob@example.com'),
            $this->logEvent($this->contentUpdatedEvent(), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(3, $filtered);
    }

    public function testFilterKeepsDraftWhenNoNextContentEvent(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftCreatedEvent(), 'bob@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
    }

    public function testFilterKeepsDiscardedDraftEvents(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent(new ContentDraftDiscardedEvent(Uuid::v4(), Uuid::v4()), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
    }

    public function testFilterKeepsStatusChangeEvents(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent(new PublicationStatusChangedEvent(Uuid::v4(), 'Object', PublicationStatus::Published), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
    }

    public function testFilterHandlesMultipleDraftsBySameAuthorBeforePublish(): void
    {
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftUpdatedEvent(), 'alice@example.com'),
            $this->logEvent($this->contentUpdatedEvent(), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        $this->assertCount(2, $filtered);
        $this->assertContainsOnlyContentEvents($filtered);
    }

    public function testFilterStopsScanAtNonDraftNonContentEvent(): void
    {
        // Draft followed by a status change (not a content event) — no "next content event"
        $events = [
            $this->logEvent($this->contentCreatedEvent(), 'alice@example.com'),
            $this->logEvent($this->draftCreatedEvent(), 'alice@example.com'),
            $this->logEvent(new PublicationStatusChangedEvent(Uuid::v4(), 'Object', PublicationStatus::Published), 'alice@example.com'),
            $this->logEvent($this->contentUpdatedEvent(), 'alice@example.com'),
        ];

        $filtered = $this->extension->filterRevisionEvents($events);

        // Draft is kept because status change breaks the scan before finding the content event
        $this->assertCount(4, $filtered);
    }

    // -- helpers --

    private function logEvent(DomainEvent $event, string $user = 'user@example.com'): LogEventInterface
    {
        return new LogEvent(Uuid::v4(), $user, 'Acme\Entity\ContentTranslation', $event);
    }

    private function contentCreatedEvent(): ContentTranslationCreatedEvent
    {
        return new ContentTranslationCreatedEvent(
            Uuid::v4(), '', 'Title', '', 'slug', [], '', 'en', new DateTimeImmutable(), null, null,
        );
    }

    private function contentUpdatedEvent(): ContentTranslationUpdatedEvent
    {
        return new ContentTranslationUpdatedEvent(
            Uuid::v4(), '', 'Title', '', 'slug', [], '', 'en', new DateTimeImmutable(),
        );
    }

    private function draftCreatedEvent(): ContentDraftCreatedEvent
    {
        return new ContentDraftCreatedEvent(
            Uuid::v4(), Uuid::v4(), '', 'Draft Title', '', 'slug', [], '', new DateTimeImmutable(),
        );
    }

    private function draftUpdatedEvent(): ContentDraftUpdatedEvent
    {
        return new ContentDraftUpdatedEvent(
            Uuid::v4(), Uuid::v4(), '', 'Draft Updated', '', 'slug', [], '', new DateTimeImmutable(),
        );
    }

    /**
     * @param list<LogEventInterface> $events
     */
    private function assertContainsOnlyContentEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->assertTrue(
                $this->extension->isContentEvent($event),
                sprintf('Expected only content events, got %s', $event->getEvent()::class),
            );
        }
    }
}
