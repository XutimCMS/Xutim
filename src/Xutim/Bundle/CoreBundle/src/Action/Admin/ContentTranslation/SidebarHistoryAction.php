<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentTranslation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Twig\Extension\RevisionExtension;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class SidebarHistoryAction extends AbstractController
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly UserRepositoryInterface $userRepository,
        private readonly RevisionExtension $revisionExtension,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $translation = $this->contentTransRepo->find($id);
        if ($translation === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }

        $allEvents = $this->eventRepository->findByTranslation($translation);
        $filteredEvents = $this->revisionExtension->filterRevisionEvents($allEvents);
        $logEventsNewestFirst = array_reverse($filteredEvents);

        $usersData = $this->userRepository->findAllUsersWithAvatars();

        return $this->render('@XutimCore/admin/revision/_sidebar_history.html.twig', [
            'events' => $logEventsNewestFirst,
            'translationId' => $translation->getId()->toRfc4122(),
            'usersData' => $usersData,
        ]);
    }
}
