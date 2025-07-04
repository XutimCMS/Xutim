<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;

#[Route('/reset-password/check-email', name: 'admin_check_email')]
class CheckEmailAction
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function __invoke(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return new Response($this->twig->render('@XutimSecurity/security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]));
    }
}
