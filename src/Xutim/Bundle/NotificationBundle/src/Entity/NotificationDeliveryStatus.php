<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Entity;

enum NotificationDeliveryStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
