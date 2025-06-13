<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Event;

use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Message\Event\DomainEventMessage;
use Xutim\CoreBundle\MessageHandler\EventHandlerInterface;
use Xutim\CoreBundle\Repository\LogEventRepository;

final class StoreDomainEventHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(DomainEventMessage $message): void
    {
        $log = $this->logEventFactory->create(
            $message->objectId,
            $message->userIdentifier,
            $message->className,
            $message->event
        );
        $this->eventRepository->save($log, true);
    }
}
