<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Entity\User;
use Xutim\EventBundle\Domain\Factory\EventFactory;
use Xutim\EventBundle\Form\Admin\EventDto;
use Xutim\EventBundle\Form\Admin\EventType;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventTranslationRepository;

#[Route('/event/new', name: 'admin_event_new', methods: ['get', 'post'])]
class CreateEventAction extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepo,
        private readonly EventTranslationRepository $eventTransRepo,
        private readonly EventFactory $eventFactory
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(EventType::class, null, [
            'action' => $this->generateUrl('admin_event_new')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventDto $eventDto */
            $eventDto = $form->getData();
            $event = $this->eventFactory->create(
                $eventDto->startsAt,
                $eventDto->endsAt,
                $eventDto->title,
                $eventDto->location,
                $eventDto->description,
                $eventDto->locale,
                $eventDto->article,
                $eventDto->page,
            );
            $translation = $event->getTranslations()->first();
            Assert::notFalse($translation);

            $this->eventTransRepo->save($translation);
            $this->eventRepo->save($event, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            return $this->redirectToRoute('admin_event_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimEvent/admin/event/event_new.html.twig', [
            'form' => $form
        ]);
    }
}
