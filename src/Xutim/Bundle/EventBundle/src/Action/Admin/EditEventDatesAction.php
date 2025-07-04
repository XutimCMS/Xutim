<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\EventBundle\Form\Admin\EventDatesType;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/event/edit-dates/{id}', name: 'admin_event_dates_edit', methods: ['get', 'post'])]
class EditEventDatesAction extends AbstractController
{
    public function __construct(private readonly EventRepository $eventRepo)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $event = $this->eventRepo->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('The event does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(EventDatesType::class, ['startsAt' => $event->getStartsAt(), 'endsAt' => $event->getEndsAt()], [
            'action' => $this->generateUrl('admin_event_dates_edit', ['id' => $event->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{startsAt: \DateTimeImmutable, endsAt: \DateTimeImmutable} $data */
            $data = $form->getData();
            $event->changeDates($data['startsAt'], $data['endsAt']);
            $this->eventRepo->save($event, true);

            $this->addFlash('success', 'Changes were made successfully.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->renderBlockView('@XutimEvent/admin/event/event_edit_dates.html.twig', 'stream_success', [
                    'event' => $event
                ]);
                $this->addFlash('stream', $stream);
            }
            $fallbackUrl = $this->generateUrl('admin_event_edit', ['id' => $event->getId()]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimEvent/admin/event/event_edit_dates.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
