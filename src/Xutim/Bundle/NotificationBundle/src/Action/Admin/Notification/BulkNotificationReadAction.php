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
final class BulkNotificationReadAction extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);

        /** @var list<string> $ids */
        $ids = $request->request->all('ids');
        $action = $request->request->getString('action');

        $user = $this->getUser();
        foreach ($ids as $id) {
            $notification = $this->notificationRepository->findOneForRecipient($id, $user);
            if ($notification === null) {
                continue;
            }

            match ($action) {
                'read' => $notification->markRead(),
                'unread' => $notification->markUnread(),
                default => null,
            };

            $this->notificationRepository->save($notification);
        }

        $this->notificationRepository->flush();

        return new RedirectResponse($this->router->generate('admin_notification_list'));
    }
}
