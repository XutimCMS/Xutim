<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Form\Admin\UserChangePasswordType;
use Xutim\CoreBundle\Message\Command\User\ChangePasswordCommand;

#[Route('/profile/change-password', name: 'admin_user_change_password')]
class UserChangePasswordAction extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $password */
            $password = $form->get('password')->getData();
            /** @var UserInterface $user */
            $user = $this->getUser();

            $encodedPassword = $this->passwordHasher->hashPassword($user, $password);

            $this->commandBus->dispatch(new ChangePasswordCommand(
                $user->getId(),
                $encodedPassword
            ));

            $this->addFlash('success', 'flash.changes_made_successfully');

            return new Response(null, 204);
        }

        return $this->render('@XutimCore/admin/user/user_change_password.html.twig', [
            'form' => $form
        ]);
    }
}
