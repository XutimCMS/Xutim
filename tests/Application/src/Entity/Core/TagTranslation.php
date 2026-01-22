<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\TagTranslation as BaseTagTranslation;

#[Entity]
#[Table(name: 'app_tag_translation')]
class TagTranslation extends BaseTagTranslation
{
}
