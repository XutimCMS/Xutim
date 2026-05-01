<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Throwable;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Service\AdminAlertService;

final readonly class FailedMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AdminAlertService $adminAlertService,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        try {
            $messageFqcn = $event->getEnvelope()->getMessage()::class;
            $throwable = $event->getThrowable();

            $deduplicationKey = sprintf('failed_job:%s:%d', $messageFqcn, intdiv(time(), 3600));

            $this->adminAlertService->notify(
                'failed_job',
                NotificationSeverity::Critical,
                sprintf('Messenger job failed: %s', $messageFqcn),
                sprintf('%s: %s', $throwable::class, $throwable->getMessage()),
                null,
                $deduplicationKey,
                [
                    'messageClass' => $messageFqcn,
                    'exceptionClass' => $throwable::class,
                    'exceptionMessage' => $throwable->getMessage(),
                ],
            );
        } catch (Throwable $exception) {
            $this->logger->error('Failed to dispatch admin alert for failed Messenger job: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
