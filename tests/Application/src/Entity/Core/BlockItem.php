<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\BlockItem as BaseBlockItem;

#[Entity]
#[Table(name: 'app_block_item')]
class BlockItem extends BaseBlockItem
{
}
