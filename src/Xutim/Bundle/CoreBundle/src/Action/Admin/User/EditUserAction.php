<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use App\Entity\Core\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Dto\Admin\User\EditUserDto;
use Xutim\CoreBundle\Form\Admin\EditUserType;
use Xutim\CoreBundle\Message\Command\User\EditUserCommand;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\ResetPasswordRequestRepository;
use Xutim\CoreBundle\Repository\UserRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/user/edit/{id}', name: 'admin_user_edit')]
class EditUserAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserRepository $userRepo
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $user = $this->userRepo->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $form = $this->createForm(
            EditUserType::class,
            new EditUserDto(
                $user->getName(),
                $user->getEmail(),
                $user->getRoles(),
                $user->getTranslationLocales()
            ),
            [
                'existing_user' => $user,
                'action' => $this->generateUrl('admin_user_edit', ['id' => $user->getId()])
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EditUserDto $dto */
            $dto = $form->getData();
            $command = new EditUserCommand(
                $user->getId(),
                $dto->name,
                $dto->roles,
                $dto->translationLocales,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            );
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $token = $this->resetPasswordRequestRepository->findOneBy(['user' => $user]);
                $stream = $this->renderBlockView('@XutimCore/admin/user/show.html.twig', 'stream_success', [
                    'user' => $user,
                    'events' => $this->eventRepository->findBy(['objectId' => $user->getId()]),
                    'resetPasswordSent' => $token !== null && $token->isExpired() === false
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('@XutimCore/admin/user/create.html.twig', [
            'form' => $form
        ]);
    }
}
