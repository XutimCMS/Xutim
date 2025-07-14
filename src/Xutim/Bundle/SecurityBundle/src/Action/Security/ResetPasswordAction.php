<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Form\ChangePasswordFormType;
use Xutim\SecurityBundle\Message\ChangePasswordCommand;

class ResetPasswordAction extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly MessageBusInterface $commandBus,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $router,
        private readonly FlashNotifier $flashNotifier,
        ContainerInterface $container
    ) {
        $this->setContainer($container);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'admin_reset_password')]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator,
        ?string $token = null
    ): Response {
        if ($token !== null && strlen($token) > 0) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return new RedirectResponse($this->router->generate('admin_reset_password'));
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw new NotFoundHttpException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var UserInterface $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->flashNotifier->flash(
                'reset_password_error',
                sprintf(
                    '%s - %s',
                    $translator->trans(
                        ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                        [],
                        'ResetPasswordBundle'
                    ),
                    $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
                )
            );

            return new RedirectResponse($this->router->generate('admin_login'));
        }

        // The token is valid; allow the user to change their password.
        $form = $this->formFactory->create(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // Encode(hash) the plain password, and set it.
            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $this->commandBus->dispatch(new ChangePasswordCommand($user->getId(), $encodedPassword));

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->flashNotifier->flash('success', 'The password was changed. You can now login with the new password.');

            return new RedirectResponse($this->router->generate('admin_login'));
        }

        return new Response(
            $this->twig->render('@XutimSecurity/security/reset_password/reset.html.twig', [
                'resetForm' => $form->createView(),
            ])
        );
    }
}
