<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Message\Notification;

use Symfony\Component\Uid\Uuid;

final readonly class SendNotificationMessage
{
    public function __construct(public Uuid $notificationId)
    {
    }
}
