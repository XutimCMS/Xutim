<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Security;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Xutim\SecurityBundle\Service\UserStorage;

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
