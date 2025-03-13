<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Message\Command\User\SendResetPasswordCommand;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/reset-password/send-token/{id}', name: 'admin_reset_password_send_token', methods: ['post'])]
class SendResetPasswordAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $this->commandBus->dispatch(new SendResetPasswordCommand($user->getId()));

        $this->addFlash('success', 'The email has been successfully sent!');

        return $this->redirect($request->headers->get('referer', ''));
    }
}
