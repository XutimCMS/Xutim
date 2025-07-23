<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Xutim\SecurityBundle\Service\UserStorage;

class LoginAction
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly UserStorage $userStorage,
        private readonly UrlGeneratorInterface $router,
        private readonly Environment $twig
    ) {
    }

    public function __invoke(): Response
    {
        if ($this->userStorage->getUser() !== null) {
            return new RedirectResponse(
                $this->router->generate('admin_homepage')
            );
        }

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return new Response(
            $this->twig->render('@XutimSecurity/security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error
            ])
        );
    }
}
