<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/profile', name: 'admin_user_profile')]
class ShowProfileAction
{
    public function __construct(
        private readonly UserStorage $userStorage,
        private readonly Environment $twig
    ) {
    }
    public function __invoke(): Response
    {
        $user = $this->userStorage->getUserWithException();

        return new Response(
            $this->twig->render('@XutimSecurity/user/profile.html.twig', [
                'user' => $user
            ])
        );
    }
}
