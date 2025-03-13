<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Event;

use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Message\Event\DomainEventMessage;
use Xutim\CoreBundle\MessageHandler\EventHandlerInterface;
use Xutim\CoreBundle\Repository\EventRepository;

final class StoreDomainEventHandler implements EventHandlerInterface
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    public function __invoke(DomainEventMessage $message): void
    {
        $log = new Event(
            $message->objectId,
            $message->userIdentifier,
            $message->className,
            $message->event
        );
        $this->eventRepository->save($log, true);
    }
}
