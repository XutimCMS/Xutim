<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;

class LogEventFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('LogEvent class "%s" does not exist.', $entityClass));
        }
    }

    public function create(
        Uuid $id,
        string $userIdentifier,
        string $className,
        DomainEvent $event
    ): LogEventInterface {
        /** @var LogEventInterface $logEvent */
        $logEvent = new ($this->entityClass)($id, $userIdentifier, $className, $event);

        return $logEvent;
    }
}
