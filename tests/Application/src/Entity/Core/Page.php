<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\Page as BasePage;

#[Entity]
#[Table(name: 'app_page')]
class Page extends BasePage
{
}
