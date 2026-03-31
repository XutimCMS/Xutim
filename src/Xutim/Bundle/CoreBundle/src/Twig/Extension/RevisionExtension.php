<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftUpdatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Event\PublicationStatus\PublicationStatusChangedEvent;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;

class RevisionExtension extends AbstractExtension
{
    private const CONTENT_EVENTS = [
        ContentTranslationCreatedEvent::class,
        ContentTranslationUpdatedEvent::class,
    ];

    private const DRAFT_EVENTS = [
        ContentDraftCreatedEvent::class,
        ContentDraftUpdatedEvent::class,
    ];

    private const COMPARABLE_EVENTS = [
        ContentTranslationCreatedEvent::class,
        ContentTranslationUpdatedEvent::class,
        ContentDraftCreatedEvent::class,
        ContentDraftUpdatedEvent::class,
    ];

    /** @var array<class-string, string> */
    private const EVENT_LABELS = [
        ContentTranslationCreatedEvent::class => 'Created',
        ContentTranslationUpdatedEvent::class => 'Updated',
        ContentDraftCreatedEvent::class => 'Draft created',
        ContentDraftUpdatedEvent::class => 'Draft updated',
        ContentDraftDiscardedEvent::class => 'Draft discarded',
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_content_event', [$this, 'isContentEvent']),
            new TwigFunction('is_comparable_event', [$this, 'isComparableEvent']),
            new TwigFunction('revision_event_label', [$this, 'getEventLabel']),
        ];
    }

    public function isContentEvent(LogEventInterface $logEvent): bool
    {
        return in_array($logEvent->getEvent()::class, self::CONTENT_EVENTS, true);
    }

    public function isComparableEvent(LogEventInterface $logEvent): bool
    {
        return in_array($logEvent->getEvent()::class, self::COMPARABLE_EVENTS, true);
    }

    public function getEventLabel(LogEventInterface $logEvent): string
    {
        $event = $logEvent->getEvent();

        if ($event instanceof PublicationStatusChangedEvent) {
            return $this->translator->trans('Status', [], 'admin')
                . ' → '
                . $this->translator->trans($event->status->value, [], 'admin');
        }

        $label = self::EVENT_LABELS[$event::class] ?? 'Event';

        return $this->translator->trans($label, [], 'admin');
    }

    /**
     * Filters events for the revision timeline (oldest-first input).
     *
     * - Content events and status changes: always shown
     * - Discarded drafts: always shown
     * - Draft created/updated: hidden when the next content event has the same
     *   author (redundant). Kept when author differs (preserves attribution)
     *   or no content event follows (active unpublished draft).
     *
     * @param array<LogEventInterface> $events oldest-first
     * @return list<LogEventInterface>
     */
    public function filterRevisionEvents(array $events): array
    {
        $result = [];

        foreach ($events as $i => $event) {
            if (!$this->isDraftEvent($event)) {
                $result[] = $event;
                continue;
            }

            $nextContentEvent = $this->findNextContentEvent($events, $i);

            if ($nextContentEvent === null
                || $nextContentEvent->getUserIdentifier() !== $event->getUserIdentifier()
            ) {
                $result[] = $event;
            }
        }

        return $result;
    }

    private function isDraftEvent(LogEventInterface $logEvent): bool
    {
        return in_array($logEvent->getEvent()::class, self::DRAFT_EVENTS, true);
    }

    /**
     * @param array<LogEventInterface> $events
     */
    private function findNextContentEvent(array $events, int $fromIndex): ?LogEventInterface
    {
        for ($j = $fromIndex + 1, $count = count($events); $j < $count; $j++) {
            $eventClass = $events[$j]->getEvent()::class;

            if (in_array($eventClass, self::CONTENT_EVENTS, true)) {
                return $events[$j];
            }

            if (!in_array($eventClass, self::DRAFT_EVENTS, true)) {
                return null;
            }
        }

        return null;
    }
}
