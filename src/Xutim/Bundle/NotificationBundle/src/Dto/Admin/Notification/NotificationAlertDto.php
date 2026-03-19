<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Dto\Admin\Notification;

use Xutim\NotificationBundle\Entity\NotificationSeverity;

final class NotificationAlertDto
{
    /**
     * @param list<string> $locales
     */
    public function __construct(
        public array $locales = [],
        public NotificationSeverity $severity = NotificationSeverity::Critical,
        public string $title = '',
        public string $message = '',
        public bool $sendEmail = true,
    ) {
    }
}
