<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentDraft;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ContentDraftInterface;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\ContentDraft\PublishContentDraftCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\ReferenceSyncService;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class PublishContentDraftHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentDraftRepository $draftRepo,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventFactory $logEventFactory,
        private LogEventRepository $eventRepository,
        private SiteContext $siteContext,
        private SearchContentBuilder $searchContentBuilder,
        private ReferenceSyncService $referenceSyncService,
    ) {
    }

    public function __invoke(PublishContentDraftCommand $cmd): void
    {
        $draft = $this->draftRepo->find($cmd->draftId);
        if ($draft === null) {
            throw new NotFoundHttpException(sprintf(
                'Content draft "%s" could not be found',
                $cmd->draftId
            ));
        }

        $translation = $draft->getTranslation();

        $contentChanged = $draft->applyToTranslation();

        if (!$contentChanged) {
            $this->discardWithoutChange($draft, $translation->getId(), $cmd->userIdentifier);

            return;
        }

        $object = $translation->getObject();
        $refLocale = $this->siteContext->getReferenceLocale();
        $isReferenceTranslation = $translation->getLocale() === $refLocale;

        if (!$isReferenceTranslation) {
            $refTrans = $object->getTranslationByLocale($refLocale);
            if ($refTrans !== null) {
                $translation->changeReferenceSyncedAt($refTrans->getUpdatedAt());
            }
        }

        $searchContent = $this->searchContentBuilder->build($translation);
        $searchTagContent = $this->searchContentBuilder->buildTagContent($translation);
        $translation->changeSearchContent($searchContent);
        $translation->changeSearchTagContent($searchTagContent);

        $hasContentRevisions = count($this->eventRepository->findContentRevisionsByTranslation($translation)) > 0;

        $event = $hasContentRevisions
            ? ContentTranslationUpdatedEvent::fromContentTranslation($translation)
            : ContentTranslationCreatedEvent::fromContentTranslation($translation);

        $log = $this->logEventFactory->create(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->draftRepo->remove($draft);
        $this->contentTransRepo->save($translation);
        $this->eventRepository->save($log);
        $this->draftRepo->flush();

        if ($isReferenceTranslation) {
            $this->referenceSyncService->resyncRevertedSiblings($translation);
        }
    }

    /**
     * Publishing a draft whose content matches the published translation makes
     * no change, so close the draft as a discard instead of logging a duplicate
     * content revision.
     */
    private function discardWithoutChange(ContentDraftInterface $draft, Uuid $translationId, string $userIdentifier): void
    {
        $event = new ContentDraftDiscardedEvent($translationId, $draft->getId());

        $log = $this->logEventFactory->create(
            $translationId,
            $userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->draftRepo->remove($draft);
        $this->eventRepository->save($log);
        $this->draftRepo->flush();
    }
}
