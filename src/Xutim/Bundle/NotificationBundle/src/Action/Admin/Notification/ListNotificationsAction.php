<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Action\Admin\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

/**
 * @method UserInterface getUser()
 */
final class ListNotificationsAction extends AbstractController
{
    public function __construct(private readonly NotificationRepository $notificationRepository)
    {
    }

    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);
        $notifications = $this->notificationRepository->findLatestForRecipient($this->getUser());

        return $this->render('@XutimNotification/admin/notification/list.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}
