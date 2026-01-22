<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\Block as BaseBlock;

#[Entity]
#[Table(name: 'app_block')]
class Block extends BaseBlock
{
}
