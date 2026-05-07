<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Xutim\CoreBundle\Message\Command\ContentTranslation\MarkRecentlyOutdatedSiblingsAsSyncedCommand;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class MarkPageTranslationsSyncedAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepo,
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly CsrfTokenChecker $csrfTokenChecker,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('xutim-dialog', $request);

        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }

        $envelope = $this->commandBus->dispatch(new MarkRecentlyOutdatedSiblingsAsSyncedCommand(
            pageId: $page->getId(),
            articleId: null,
            userIdentifier: $this->userStorage->getUserWithException()->getUserIdentifier(),
        ));
        $count = $envelope->last(HandledStamp::class)?->getResult() ?? 0;

        $this->addFlash('success', $this->buildFlashMessage($count));

        return $this->redirect($request->headers->get('referer', ''));
    }

    private function buildFlashMessage(int $count): string
    {
        if ($count === 0) {
            return 'flash.no_translations_to_sync';
        }
        return 'flash.translations_marked_synced';
    }
}
