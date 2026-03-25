<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

/**
 * When the reference translation is saved, check each sibling to see if
 * the reference content effectively changed from their point of view.
 *
 * For each sibling, compare the reference revision at the sibling's
 * referenceSyncedAt (the content the sibling already "knows about")
 * with the current reference content. If identical, the reference went
 * through one or more changes but ended up back where the sibling last
 * saw it (e.g. a change followed by a revert). In that case, bump the
 * sibling's referenceSyncedAt so translators don't see a false
 * "reference has changed" banner with no actual diff.
 */
readonly class ReferenceSyncService
{
    public function __construct(
        private LogEventRepository $eventRepository,
        private ContentTranslationRepository $contentTransRepo,
    ) {
    }

    public function resyncRevertedSiblings(ContentTranslationInterface $reference): void
    {
        $object = $reference->getObject();

        foreach ($object->getTranslations() as $sibling) {
            if ($sibling->getId() === $reference->getId()) {
                continue;
            }

            $syncedAt = $sibling->getReferenceSyncedAt();
            if ($syncedAt === null || $syncedAt >= $reference->getUpdatedAt()) {
                continue;
            }

            $oldRevision = $this->eventRepository->findRevisionAtOrBefore($reference, $syncedAt);
            if ($oldRevision === null) {
                continue;
            }

            /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $oldEvent */
            $oldEvent = $oldRevision->getEvent();

            $contentUnchanged = $oldEvent->preTitle === $reference->getPreTitle()
                && $oldEvent->title === $reference->getTitle()
                && $oldEvent->subTitle === $reference->getSubTitle()
                && $oldEvent->description === $reference->getDescription()
                && ($oldEvent->content['blocks'] ?? []) === ($reference->getContent()['blocks'] ?? []);

            if ($contentUnchanged) {
                $sibling->changeReferenceSyncedAt($reference->getUpdatedAt());
                $this->contentTransRepo->save($sibling);
            }
        }
    }
}
