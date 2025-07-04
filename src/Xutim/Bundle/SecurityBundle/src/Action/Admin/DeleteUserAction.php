<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Message\DeleteUserCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/user/delete/{id}', name: 'admin_user_delete', methods: ['post'])]
class DeleteUserAction
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly UrlGeneratorInterface $router,
        private readonly FlashNotifier $flashNotifier,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }

        $user = $this->userRepo->findById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }

        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $command = new DeleteUserCommand($user->getId(), $user->getUserIdentifier());
        $this->commandBus->dispatch($command);
        $this->flashNotifier->changesSaved();


        return new RedirectResponse(
            $this->router->generate('admin_user_list')
        );
    }
}
