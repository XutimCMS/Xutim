<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentDraft;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\ContentDraft\ContentDraftDiscardedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\ContentDraft\DiscardContentDraftCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

readonly class DiscardContentDraftHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentDraftRepository $draftRepo,
        private LogEventFactory $logEventFactory,
        private LogEventRepository $eventRepository,
    ) {
    }

    public function __invoke(DiscardContentDraftCommand $cmd): void
    {
        $draft = $this->draftRepo->find($cmd->draftId);
        if ($draft === null) {
            throw new NotFoundHttpException(sprintf(
                'Content draft "%s" could not be found',
                $cmd->draftId
            ));
        }

        $translationId = $draft->getTranslation()->getId();

        $this->draftRepo->remove($draft, true);

        $event = new ContentDraftDiscardedEvent($translationId, $cmd->draftId);

        $log = $this->logEventFactory->create(
            $translationId,
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->eventRepository->save($log, true);
    }
}
