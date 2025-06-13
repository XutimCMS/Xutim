<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use App\Entity\Core\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Page\PageDefaultTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/page/{id}/mark-default-translation/{transId}', name: 'admin_page_mark_default_translation')]
class MarkDefaultTranslationAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly PageRepository $pageRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, string $id, string $transId): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $trans = $this->contentTransRepo->find($transId);
        if ($trans === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $page->setDefaultTranslation($trans);
        $this->pageRepo->save($page, true);

        $event = new PageDefaultTranslationUpdatedEvent($page->getId(), $trans->getId());
        $logEntry = $this->logEventFactory->create(
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
