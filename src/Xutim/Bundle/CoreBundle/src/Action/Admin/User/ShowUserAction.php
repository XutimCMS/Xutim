<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\ResetPasswordRequestRepository;

#[Route('/user/{id}', name: 'admin_user_show')]
class ShowUserAction extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository
    ) {
    }

    public function __invoke(User $user): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $events = $this->eventRepository->findBy(['objectId' => $user->getId()]);
        $token = $this->resetPasswordRequestRepository->findOneBy(['user' => $user]);


        return $this->render('@XutimCore/admin/user/show.html.twig', [
            'user' => $user,
            'events' => $events,
            'resetPasswordSent' => $token !== null && $token->isExpired() === false
        ]);
    }
}
