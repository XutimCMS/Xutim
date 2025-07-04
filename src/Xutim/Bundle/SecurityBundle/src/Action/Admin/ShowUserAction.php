<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Action\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Repository\ResetPasswordRequestRepository;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

#[Route('/user/{id}', name: 'admin_user_show')]
class ShowUserAction
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserRepositoryInterface $userRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(string $id): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_ADMIN) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $user = $this->userRepo->findById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The user does not exist');
        }
        $events = $this->eventRepository->findBy(['objectId' => $user->getId()]);
        $token = $this->resetPasswordRequestRepository->findOneBy(['user' => $user]);

        $html = $this->twig->render('@XutimSecurity/user/show.html.twig', [
            'user' => $user,
            'events' => $events,
            'resetPasswordSent' => $token !== null && $token->isExpired() === false
        ]);

        return new Response($html);
    }
}
