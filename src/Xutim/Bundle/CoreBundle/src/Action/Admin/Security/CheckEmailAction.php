<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password/check-email', name: 'admin_check_email')]
class CheckEmailAction extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(private readonly ResetPasswordHelperInterface $resetPasswordHelper)
    {
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

        return $this->render('@XutimCore/admin/security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }
}
