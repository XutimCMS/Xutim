<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\Site as BaseSite;

#[Entity]
#[Table(name: 'app_site')]
class Site extends BaseSite
{
}
