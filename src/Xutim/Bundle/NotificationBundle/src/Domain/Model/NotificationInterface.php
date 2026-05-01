<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\NotificationBundle\Entity\NotificationDeliveryStatus;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

interface NotificationInterface
{
    public function getId(): Uuid;

    public function getRecipient(): ?UserInterface;

    public function getRecipientId(): ?Uuid;

    public function getRecipientEmail(): ?string;

    public function getRecipientAddress(): string;

    public function getType(): string;

    public function getSeverity(): NotificationSeverity;

    public function getTitle(): string;

    public function getBody(): string;

    public function getActionUrl(): ?string;

    public function getActionLabel(): ?string;

    /**
     * @return list<string>
     */
    public function getChannels(): array;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    public function getDeduplicationKey(): ?string;

    public function getDeliveryStatus(): NotificationDeliveryStatus;

    public function markRead(): void;

    public function markUnread(): void;

    public function markSent(): void;

    public function markFailed(): void;

    public function isRead(): bool;

    public function getReadAt(): ?DateTimeImmutable;

    public function getCreatedAt(): DateTimeImmutable;
}
