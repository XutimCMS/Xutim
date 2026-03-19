<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Action\Admin\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;

/**
 * @method UserInterface getUser()
 */
final class MarkAllNotificationsReadAction extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);
        foreach ($this->notificationRepository->findLatestForRecipient($this->getUser(), 200) as $notification) {
            $notification->markRead();
            $this->notificationRepository->save($notification);
        }

        $this->notificationRepository->flush();

        return new RedirectResponse($this->router->generate('admin_notification_list'));
    }
}
