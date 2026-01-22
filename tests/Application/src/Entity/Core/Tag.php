<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\Tag as BaseTag;

#[Entity]
#[Table(name: 'app_tag')]
class Tag extends BaseTag
{
}
