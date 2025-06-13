<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Message\Command\User\DeleteUserCommand;
use Xutim\CoreBundle\Repository\UserRepository;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/user/delete/{id}', name: 'admin_user_delete', methods: ['post'])]
class DeleteUserAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly UserRepository $userRepo
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $user = $this->userRepo->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $this->denyAccessUnlessGranted(UserInterface::ROLE_ADMIN);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $command = new DeleteUserCommand($user->getId(), $user->getUserIdentifier());
        $this->commandBus->dispatch($command);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirectToRoute('admin_user_list');
    }
}
