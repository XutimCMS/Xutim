<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Domain\Factory;

use Xutim\NotificationBundle\Domain\Model\NotificationInterface;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

final class NotificationFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Notification class "%s" does not exist.', $entityClass));
        }
    }

    /**
     * @param list<string>         $channels
     * @param array<string, mixed> $payload
     */
    public function create(
        UserInterface|string $recipient,
        string $type,
        NotificationSeverity $severity,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $channels = ['database'],
        array $payload = [],
        ?string $deduplicationKey = null,
    ): NotificationInterface {
        /** @var NotificationInterface $notification */
        $notification = new ($this->entityClass)(
            $recipient,
            $type,
            $severity,
            $title,
            $body,
            $actionUrl,
            $actionLabel,
            $channels,
            $payload,
            $deduplicationKey,
        );

        return $notification;
    }
}
