<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Action\Admin\Notification;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;

/**
 * @method UserInterface getUser()
 */
final class ListNotificationsAction extends AbstractController
{
    public function __construct(private readonly NotificationRepository $notificationRepository)
    {
    }

    public function __invoke(#[MapQueryParameter] int $page = 1): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);

        $qb = $this->notificationRepository->createForRecipientQueryBuilder($this->getUser());

        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($qb),
            $page,
            20
        );

        return $this->render('@XutimNotification/admin/notification/list.html.twig', [
            'notifications' => $pager,
        ]);
    }
}
