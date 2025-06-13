<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\DomainEvent;
use Xutim\CoreBundle\Domain\Model\LogEventInterface;

#[MappedSuperclass]
class LogEvent implements LogEventInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'uuid')]
    private Uuid $objectId;

    #[Column(type: 'string')]
    private string $userIdentifier;

    #[Column(length: 255)]
    private string $rootEntity;

    #[Column(type: 'json_document', options: ['jsonb' => true])]
    private DomainEvent $event;

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $recordedAt;

    public function __construct(
        Uuid $objectId,
        string $userIdentifier,
        string $rootEntity,
        DomainEvent $event
    ) {
        $this->id = Uuid::v4();
        $this->objectId = $objectId;
        $this->userIdentifier = $userIdentifier;
        $this->rootEntity = $rootEntity;
        $this->event = $event;
        $this->recordedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEvent(): DomainEvent
    {
        return $this->event;
    }

    public function getRecordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
