<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Service;

use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\NotificationBundle\Domain\Factory\NotificationFactory;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Message\Notification\SendNotificationMessage;
use Xutim\NotificationBundle\Repository\NotificationRepository;

final readonly class AdminAlertService
{
    private const string DEDUP_WINDOW = '-1 hour';

    public function __construct(
        private SiteContext $siteContext,
        private NotificationRepository $notificationRepository,
        private NotificationFactory $notificationFactory,
        private MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function notify(
        string $type,
        NotificationSeverity $severity,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $deduplicationKey = null,
        array $payload = [],
    ): void {
        $emails = $this->siteContext->getAdminAlertEmails();
        if ($emails === []) {
            return;
        }

        if ($deduplicationKey !== null) {
            $since = new DateTimeImmutable(self::DEDUP_WINDOW);
            if ($this->notificationRepository->findRecentByDeduplicationKey($deduplicationKey, $since) !== null) {
                return;
            }
        }

        foreach ($emails as $email) {
            $notification = $this->notificationFactory->create(
                $email,
                $type,
                $severity,
                $title,
                $body,
                $actionUrl,
                null,
                ['email'],
                $payload,
                $deduplicationKey,
            );

            $this->notificationRepository->save($notification);
            $this->commandBus->dispatch(new SendNotificationMessage($notification->getId()));
        }

        $this->notificationRepository->flush();
    }
}
