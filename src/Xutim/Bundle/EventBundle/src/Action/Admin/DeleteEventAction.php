<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventTranslationRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

class DeleteEventAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly EventRepository $eventRepo,
        private readonly EventTranslationRepository $eventTransRepo,
        private readonly AdminUrlGenerator $router
    ) {
    }

    public function __invoke(string $id, Request $request): Response
    {
        $event = $this->eventRepo->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('The event does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        foreach ($event->getTranslations() as $trans) {
            $this->eventTransRepo->remove($trans);
        }
        $this->eventRepo->remove($event);

        $this->addFlash('success', 'flash.changes_made_successfully');

        $url = $this->router->generate('admin_event_list', ['searchTerm' => '']);

        return new RedirectResponse($url);
    }
}
