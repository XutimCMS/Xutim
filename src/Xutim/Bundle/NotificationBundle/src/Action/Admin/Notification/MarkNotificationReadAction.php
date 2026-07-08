<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Action\Admin\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;
use Xutim\SecurityBundle\Security\UserRoles;

/**
 * @method UserInterface getUser()
 */
final class MarkNotificationReadAction extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);
        $notification = $this->notificationRepository->findOneForRecipient($id, $this->getUser());
        if ($notification !== null) {
            $notification->markRead();
            $this->notificationRepository->save($notification, true);
        }

        $referer = $request->headers->get('referer', '');
        if ($referer !== '') {
            return new RedirectResponse($referer);
        }

        return new RedirectResponse($this->router->generate('admin_notification_list'));
    }
}
