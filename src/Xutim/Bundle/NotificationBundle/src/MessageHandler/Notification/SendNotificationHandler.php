<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\MessageHandler\Notification;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\NotificationBundle\Message\Notification\SendNotificationMessage;
use Xutim\NotificationBundle\Repository\NotificationRepository;

final readonly class SendNotificationHandler implements CommandHandlerInterface
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendNotificationMessage $message): void
    {
        $notification = $this->notificationRepository->find($message->notificationId);
        if ($notification === null) {
            return;
        }

        if (!in_array('email', $notification->getChannels(), true)) {
            return;
        }

        try {
            $templateBase = $notification->getRecipient() === null
                ? '@XutimNotification/admin/notification/admin_alert_email'
                : '@XutimNotification/admin/notification/email';

            $email = (new TemplatedEmail())
                ->to($notification->getRecipientAddress())
                ->subject($notification->getTitle())
                ->htmlTemplate($templateBase . '.html.twig')
                ->textTemplate($templateBase . '.txt.twig')
                ->context([
                    'notification' => $notification,
                ]);

            $this->mailer->send($email);
            $notification->markSent();
            $this->notificationRepository->save($notification, true);
        } catch (\Throwable $exception) {
            $notification->markFailed();
            $this->notificationRepository->save($notification, true);
            $this->logger->warning('Notification email delivery failed: {message}', [
                'message' => $exception->getMessage(),
                'notificationId' => $notification->getId()->toRfc4122(),
            ]);
        }
    }
}
