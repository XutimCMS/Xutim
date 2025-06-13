<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use App\Entity\Event\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Service\CsrfTokenChecker;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventTranslationRepository;

#[Route('/event/delete/{id}', name: 'admin_event_delete')]
class DeleteEventAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly EventRepository $eventRepo,
        private readonly EventTranslationRepository $eventTransRepo,
    ) {
    }

    public function __invoke(Event $event, Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        foreach ($event->getTranslations() as $trans) {
            $this->eventTransRepo->remove($trans);
        }
        $this->eventRepo->remove($event);

        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirectToRoute('admin_event_list', ['searchTerm' => '']);
    }
}
