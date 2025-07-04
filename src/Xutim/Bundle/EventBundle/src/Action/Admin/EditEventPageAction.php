<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\EventBundle\Form\Admin\EventPageType;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/event/edit-page/{id}', name: 'admin_event_page_edit', methods: ['get', 'post'])]
class EditEventPageAction extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepo,
        private readonly PageRepository $pageRepo,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $event = $this->eventRepo->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('The event does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $id = $event->getPage()?->getId();
        $form = $this->createForm(EventPageType::class, ['page' => $id], [
            'action' => $this->generateUrl('admin_event_page_edit', ['id' => $event->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{page: ?string} $data */
            $data = $form->getData();

            $page = null;
            if ($data['page'] !== null) {
                $page = $this->pageRepo->find($data['page']);
                if ($page === null) {
                    throw new TransformationFailedException(
                        sprintf(
                            'The selected page "%s" does not exist.',
                            $data['page']
                        )
                    );
                }
            }
            $event->changePage($page);
            $this->eventRepo->save($event, true);

            $this->addFlash('success', 'Changes were made successfully.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->renderBlockView('@XutimEvent/admin/event/event_edit_page.html.twig', 'stream_success', [
                    'event' => $event
                ]);
                $this->addFlash('stream', $stream);
            }
            $fallbackUrl = $this->generateUrl('admin_event_edit', ['id' => $event->getId()]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimEvent/admin/event/event_edit_page.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
