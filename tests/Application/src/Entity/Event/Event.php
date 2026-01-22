<?php

declare(strict_types=1);

namespace App\Entity\Event;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\EventBundle\Entity\Event as BaseEvent;

#[Entity]
#[Table(name: 'app_event')]
class Event extends BaseEvent
{
}
