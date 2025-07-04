<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin\User;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Message\SendResetPasswordCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/reset-password/send-token/{id}', name: 'admin_reset_password_send_token', methods: ['post'])]
class SendResetPasswordAction
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly MessageBusInterface $commandBus,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly FlashNotifier $flashNotifier,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $user = $this->userRepo->findById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $this->commandBus->dispatch(new SendResetPasswordCommand($user->getId()));

        $this->flashNotifier->changesSaved();

        return new RedirectResponse($request->headers->get('referer', ''));
    }
}
