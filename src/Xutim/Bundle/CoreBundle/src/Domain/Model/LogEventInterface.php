<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;

interface LogEventInterface
{
    public function getId(): Uuid;

    public function getEvent(): DomainEvent;

    public function getRecordedAt(): DateTimeImmutable;

    public function getUserIdentifier(): string;
}
