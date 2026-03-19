<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Service;

use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Service\UserStorage;

final class NotificationCenterView
{
    private ?int $cachedUnreadCount = null;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly UserStorage $userStorage,
    ) {
    }

    public function getUnreadCount(): int
    {
        if ($this->cachedUnreadCount !== null) {
            return $this->cachedUnreadCount;
        }

        $user = $this->userStorage->getUser();
        if ($user === null) {
            return 0;
        }

        return $this->cachedUnreadCount = $this->notificationRepository->countUnreadForRecipient($user);
    }
}
