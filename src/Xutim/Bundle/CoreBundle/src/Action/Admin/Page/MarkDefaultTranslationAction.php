<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Page\PageDefaultTranslationUpdatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/page/{id}/mark-default-translation/{transId}', name: 'admin_page_mark_default_translation')]
class MarkDefaultTranslationAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly PageRepository $repository,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(
        Request $request,
        Page $page,
        #[MapEntity(mapping: ['transId' => 'id'])]
        ContentTranslation $trans
    ): Response {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $page->setDefaultTranslation($trans);
        $this->repository->save($page, true);

        $event = new PageDefaultTranslationUpdatedEvent($page->getId(), $trans->getId());
        $logEntry = new Event(
            $page->getId(),
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            Page::class,
            $event
        );
        $this->eventRepository->save($logEntry, true);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirect($request->headers->get('referer', ''));
    }
}
