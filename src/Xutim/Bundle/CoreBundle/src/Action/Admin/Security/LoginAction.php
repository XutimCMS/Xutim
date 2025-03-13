<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/login', name: 'admin_login')]
class LoginAction extends AbstractController
{
    public function __construct(private readonly AuthenticationUtils $authenticationUtils)
    {
    }

    public function __invoke(): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('admin_homepage');
        }

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('@XutimCore/admin/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}
