<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationUpdatedEvent;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\EditorJsDiffRenderer;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class ShowTranslationRevisionsAction extends AbstractController
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly EditorJsDiffRenderer $diffRenderer,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ContentTranslationRepository $contentTransRepo,
    ) {
    }

    public function __invoke(string $id, ?string $oldId = null, ?string $newId = null): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }

        if (($oldId === null && $newId !== null) || ($oldId !== null && $newId === null)) {
            throw $this->createNotFoundException('Either both (oldId and newId) should be set or both have to be null.');
        }

        $logEvents = $this->eventRepository->findByTranslation($translation);
        $logEventsNewestFirst = array_values(array_reverse($logEvents));
        if ($oldId === null && $newId === null) {
            $newRevision = $logEventsNewestFirst[0];
            $oldRevision = $logEventsNewestFirst[count($logEvents) === 1 ? 0 : 1];
        } else {
            $oldRevision = $this->eventRepository->find($oldId);
            $newRevision = $this->eventRepository->find($newId);
            if ($oldRevision === null || $newRevision === null) {
                throw $this->createNotFoundException('The revision with "' . $oldId . '" or "' . $newId . '" does not exist.');
            }
        }

        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $newEvent */
        $newEvent = $newRevision->getEvent();
        /** @var ContentTranslationCreatedEvent|ContentTranslationUpdatedEvent $oldEvent */
        $oldEvent = $oldRevision->getEvent();

        $titleDiff = $this->diffRenderer->diffTitle(
            $oldEvent->title,
            $newEvent->title
        );
        $descriptionDiff = $this->diffRenderer->diffDescription(
            $oldEvent->description,
            $newEvent->description
        );

        $contentRows = $this->diffRenderer->diffContent(
            $oldEvent->content,
            $newEvent->content,
        );
        $usernames = $this->userRepository->findAllUsernamesByEmail();

        return $this->render('@XutimCore/admin/revision/translation_revisions.html.twig', [
            'translation' => $translation,
            'titleDiff' => $titleDiff,
            'selectedOld' => $oldRevision,
            'selectedNew' => $newRevision,
            'descriptionDiff' => $descriptionDiff,
            'contentRows' => $contentRows,
            'events' => $logEventsNewestFirst,
            'usernames' => $usernames,
        ]);
    }
}
