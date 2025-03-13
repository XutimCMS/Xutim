<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;

#[Route('/profile', name: 'admin_user_profile')]
class ShowProfileAction extends AbstractController
{
    public function __invoke(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new NotFoundHttpException('User is not authenticated.');
        }

        return $this->render('@XutimCore/admin/user/profile.html.twig', [
            'user' => $user
        ]);
    }
}
