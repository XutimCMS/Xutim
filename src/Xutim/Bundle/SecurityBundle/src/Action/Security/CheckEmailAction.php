<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;

class CheckEmailAction
{
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    public function __invoke(Request $request): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        $session = $request->getSession();
        $resetToken = $session->get('ResetPasswordToken');
        if (null === $resetToken) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return new Response($this->twig->render('@XutimSecurity/security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]));
    }
}
