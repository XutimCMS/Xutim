<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\User;

use App\Entity\Core\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\ResetPasswordRequestRepository;
use Xutim\CoreBundle\Repository\UserRepository;

#[Route('/user/{id}', name: 'admin_user_show')]
class ShowUserAction extends AbstractController
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserRepository $userRepo
    ) {
    }

    public function __invoke(string $id): Response
    {
        $user = $this->userRepo->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('The user does not exist');
        }
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
