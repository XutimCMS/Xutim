<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Service\CsrfTokenChecker;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;

#[Route(
    '/publication-status/event/edit/{id}/{status}',
    name: 'admin_event_publication_status_edit',
    requirements: ['status' => new EnumRequirement(PublicationStatus::class)],
    methods: ['post']
)]
class EditEventStatusAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly EventRepository $eventRepo
    ) {
    }

    public function __invoke(
        Request $request,
        string $id,
        PublicationStatus $status
    ): Response {
        $event = $this->eventRepo->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('The event does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $event->changeStatus($status);
        $this->eventRepo->save($event, true);

        $this->addFlash('success', 'flash.changes_made_successfully');

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $stream = $this->renderBlockView('@XutimEvent/admin/event/_event_edit_status.html.twig', 'stream_success', [
                'event' => $event
            ]);
            $this->addFlash('stream', $stream);

            return $this->redirect($request->headers->get('referer', '/'));
        }

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
