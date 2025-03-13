<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\CreateUserType;
use Xutim\CoreBundle\Message\Command\User\CreateUserCommand;
use Xutim\CoreBundle\Repository\UserRepository;

#[Route('/user/new', name: 'admin_user_new')]
class CreateUserAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserRepository $userRepo
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $form = $this->createForm(CreateUserType::class, null, [
            'action' => $this->generateUrl('admin_user_new')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateUserCommand $command */
            $command = $form->getData();
            $email = $command->email;
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/user/create.html.twig', 'stream_success', [
                    'user' => $this->userRepo->findOneBy(['email' => $email])
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_user_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimCore/admin/user/create.html.twig', [
            'form' => $form
        ]);
    }
}
