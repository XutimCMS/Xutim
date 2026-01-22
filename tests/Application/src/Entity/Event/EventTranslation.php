<?php

declare(strict_types=1);

namespace App\Entity\Event;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\EventBundle\Entity\EventTranslation as BaseEventTranslation;

#[Entity]
#[Table(name: 'app_event_translation')]
class EventTranslation extends BaseEventTranslation
{
}
