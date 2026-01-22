<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\MenuItem as BaseMenuItem;

#[Entity]
#[Table(name: 'app_menu_item')]
class MenuItem extends BaseMenuItem
{
}
