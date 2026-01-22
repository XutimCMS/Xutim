<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\LogEvent as BaseLogEvent;

#[Entity]
#[Table(name: 'app_log_event')]
class LogEvent extends BaseLogEvent
{
}
