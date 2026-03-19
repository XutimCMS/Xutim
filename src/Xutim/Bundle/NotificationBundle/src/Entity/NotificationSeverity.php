<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Entity;

enum NotificationSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';

    public function shouldEmailByDefault(): bool
    {
        return $this === self::Critical;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Info => 'secondary',
            self::Warning => 'warning',
            self::Critical => 'danger',
        };
    }

    public function statusColor(): string
    {
        return match ($this) {
            self::Info => 'azure',
            self::Warning => 'yellow',
            self::Critical => 'red',
        };
    }

    public function iconName(): string
    {
        return match ($this) {
            self::Info => 'tabler:info-circle',
            self::Warning => 'tabler:alert-triangle',
            self::Critical => 'tabler:alert-octagon',
        };
    }
}
