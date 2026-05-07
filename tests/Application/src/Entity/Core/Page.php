<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Xutim\CoreBundle\Entity\Page as BasePage;

#[Entity]
#[Table(name: 'app_page')]
#[UniqueConstraint(name: 'uniq_page_parent_position', columns: ['parent_id', 'position'])]
class Page extends BasePage
{
}
