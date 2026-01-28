<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\ContentDraft;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Message\Command\ContentDraft\PublishContentDraftCommand;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

class PublishDraftAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly ContentDraftRepository $draftRepo,
        private readonly TranslatorAuthChecker $transAuthChecker,
    ) {
    }

    public function __invoke(Request $request, string $draftId): Response
    {
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $draft = $this->draftRepo->find(Uuid::fromString($draftId));
        if ($draft === null) {
            throw new NotFoundHttpException(sprintf('Content draft "%s" could not be found', $draftId));
        }

        $this->transAuthChecker->denyUnlessCanTranslate($draft->getTranslation()->getLocale());

        $user = $this->userStorage->getUserWithException();

        $this->commandBus->dispatch(new PublishContentDraftCommand(
            $draft->getId(),
            $user->getUserIdentifier(),
        ));

        $this->addFlash('success', 'Draft published successfully.');

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
