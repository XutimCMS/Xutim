<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\EventBundle\Domain\Model\EventInterface;
use Xutim\EventBundle\Domain\Model\EventTranslationInterface;

#[MappedSuperclass]
class EventTranslation implements EventTranslationInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private string $title;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private string $location;

    #[Column(type: Types::TEXT, nullable: false)]
    private string $description;

    #[Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[ManyToOne(targetEntity: EventInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
    private EventInterface $event;

    public function __construct(
        string $title,
        string $location,
        string $description,
        string $locale,
        EventInterface $event
    ) {
        $this->id = Uuid::v4();
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->title = $title;
        $this->location = $location;
        $this->description = $description;
        $this->locale = $locale;
        $this->event = $event;
    }

    public function change(
        string $title,
        string $location,
        string $description,
    ): void {
        $this->updatedAt = new DateTimeImmutable();
        $this->title = $title;
        $this->location = $location;
        $this->description = $description;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getEvent(): EventInterface
    {
        return $this->event;
    }
}
