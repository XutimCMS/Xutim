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
final class MarkNotificationReadAction extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_USER);
        $notification = $this->notificationRepository->findOneForRecipient($id, $this->getUser());
        if ($notification !== null) {
            $notification->markRead();
            $this->notificationRepository->save($notification, true);

            $payload = $notification->getPayload();
            if (isset($payload['routeName'], $payload['routeParameters'])
                && is_string($payload['routeName'])
                && str_starts_with($payload['routeName'], 'admin_')
                && is_array($payload['routeParameters'])
            ) {
                /** @var array<string, mixed> $routeParameters */
                $routeParameters = $payload['routeParameters'];
                return new RedirectResponse($this->router->generate($payload['routeName'], $routeParameters));
            }

            if ($notification->getActionUrl() !== null && str_starts_with($notification->getActionUrl(), '/')) {
                return new RedirectResponse($notification->getActionUrl());
            }
        }

        return new RedirectResponse($this->router->generate('admin_notification_list'));
    }
}
