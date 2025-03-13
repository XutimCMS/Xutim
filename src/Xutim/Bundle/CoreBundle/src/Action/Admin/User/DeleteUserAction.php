<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\User\DeleteUserCommand;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/user/delete/{id}', name: 'admin_user_delete', methods: ['post'])]
class DeleteUserAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly CsrfTokenChecker $csrfTokenChecker
    ) {
    }

    public function __invoke(Request $request, User $userToDelete): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $user = $this->userStorage->getUserWithException();
        $command = new DeleteUserCommand($userToDelete->getId(), $user->getUserIdentifier());
        $this->commandBus->dispatch($command);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirectToRoute('admin_user_list');
    }
}
