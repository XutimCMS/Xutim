<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Form\Admin\ResetPasswordRequestFormType;
use Xutim\CoreBundle\Repository\UserRepository;

#[Route('/reset-password', name: 'admin_forgot_password_request')]
class ForgotPasswordRequestAction extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UserRepository $userRepository,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return $this->render('@XutimCore/admin/security/reset_password/request.html.twig', [
            'requestForm' => $form
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData): RedirectResponse
    {
        $user = $this->userRepository->findOneBy([
            'email' => $emailFormData
        ]);

        // Do not reveal whether a user account was found or not.
        if ($user === null) {
            return $this->redirectToRoute('admin_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('admin_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->siteContext->getSender(), 'Taizé Website'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('@XutimCore/admin/security/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
        $this->mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('admin_check_email');
    }
}
