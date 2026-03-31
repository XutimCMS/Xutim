<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;
use Xutim\NotificationBundle\Domain\Model\NotificationInterface;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

#[MappedSuperclass]
#[Index(name: 'xutim_notification_recipient_created_idx', columns: ['recipient_id', 'created_at'])]
#[Index(name: 'xutim_notification_recipient_read_idx', columns: ['recipient_id', 'read_at'])]
class Notification implements NotificationInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserInterface $recipient;

    #[Column(type: Types::STRING, length: 120)]
    private string $type;

    #[Column(type: Types::STRING, length: 20, enumType: NotificationSeverity::class)]
    private NotificationSeverity $severity;

    #[Column(type: Types::STRING, length: 255)]
    private string $title;

    #[Column(type: Types::TEXT)]
    private string $body;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $actionUrl;

    #[Column(type: Types::STRING, length: 120, nullable: true)]
    private ?string $actionLabel;

    /** @var list<string> */
    #[Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $channels;

    /** @var array<string, mixed> */
    #[Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $payload;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $deduplicationKey;

    #[Column(type: Types::STRING, length: 20, enumType: NotificationDeliveryStatus::class)]
    private NotificationDeliveryStatus $deliveryStatus;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $failedAt = null;

    /**
     * @param list<string>         $channels
     * @param array<string, mixed> $payload
     */
    public function __construct(
        UserInterface $recipient,
        string $type,
        NotificationSeverity $severity,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $channels = ['database'],
        array $payload = [],
        ?string $deduplicationKey = null,
    ) {
        $this->id = Uuid::v4();
        $this->recipient = $recipient;
        $this->type = $type;
        $this->severity = $severity;
        $this->title = $title;
        $this->body = $body;
        $this->actionUrl = $actionUrl;
        $this->actionLabel = $actionLabel;
        $this->channels = array_values(array_unique($channels));
        $this->payload = $payload;
        $this->deduplicationKey = $deduplicationKey;
        $this->deliveryStatus = in_array('email', $this->channels, true)
            ? NotificationDeliveryStatus::Pending
            : NotificationDeliveryStatus::Sent;
        $this->createdAt = new DateTimeImmutable();
        $this->sentAt = $this->deliveryStatus === NotificationDeliveryStatus::Sent ? $this->createdAt : null;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRecipient(): UserInterface
    {
        return $this->recipient;
    }

    public function getRecipientId(): Uuid
    {
        return $this->recipient->getId();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSeverity(): NotificationSeverity
    {
        return $this->severity;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getActionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getDeduplicationKey(): ?string
    {
        return $this->deduplicationKey;
    }

    public function getDeliveryStatus(): NotificationDeliveryStatus
    {
        return $this->deliveryStatus;
    }

    public function markRead(): void
    {
        if ($this->readAt !== null) {
            return;
        }

        $this->readAt = new DateTimeImmutable();
    }

    public function markUnread(): void
    {
        $this->readAt = null;
    }

    public function markSent(): void
    {
        $this->deliveryStatus = NotificationDeliveryStatus::Sent;
        $this->sentAt = new DateTimeImmutable();
        $this->failedAt = null;
    }

    public function markFailed(): void
    {
        $this->deliveryStatus = NotificationDeliveryStatus::Failed;
        $this->failedAt = new DateTimeImmutable();
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
