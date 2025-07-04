<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SecurityBundle\Form\ResetPasswordRequestFormType;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

#[Route('/reset-password', name: 'admin_forgot_password_request')]
class ForgotPasswordRequestAction
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UserRepositoryInterface $userRepo,
        private readonly Environment $twig,
        private readonly SiteContext $siteContext,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return new Response(
            $this->twig->render('@XutimSecurity/security/reset_password/request.html.twig', [
                'requestForm' => $form->createView()
            ])
        );
    }

    private function processSendingPasswordResetEmail(string $emailFormData): RedirectResponse
    {
        $user = $this->userRepo->findOneByEmail($emailFormData);

        // Do not reveal whether a user account was found or not.
        if ($user === null) {
            return new RedirectResponse($this->router->generate('admin_check_email'));
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return new RedirectResponse($this->router->generate('admin_check_email'));
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->siteContext->getSender(), 'Taizé Website'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('@XutimSecurity/security/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
        $this->mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return new RedirectResponse($this->router->generate('admin_check_email'));
    }
}
