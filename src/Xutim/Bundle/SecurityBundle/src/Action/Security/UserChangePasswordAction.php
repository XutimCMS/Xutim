<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Form\UserChangePasswordType;
use Xutim\SecurityBundle\Message\ChangePasswordCommand;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/profile/change-password', name: 'admin_user_change_password')]
class UserChangePasswordAction
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MessageBusInterface $commandBus,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly FlashNotifier $flashNotifier,
        private readonly UserStorage $userStorage
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $password */
            $password = $form->get('password')->getData();
            $user = $this->userStorage->getUserWithException();

            $encodedPassword = $this->passwordHasher->hashPassword($user, $password);

            $this->commandBus->dispatch(new ChangePasswordCommand(
                $user->getId(),
                $encodedPassword
            ));

            $this->flashNotifier->changesSaved();

            return new Response(null, 204);
        }

        return new Response(
            $this->twig->render('@XutimSecurity/user/user_change_password.html.twig', [
                'form' => $form->createView()
            ])
        );
    }
}
