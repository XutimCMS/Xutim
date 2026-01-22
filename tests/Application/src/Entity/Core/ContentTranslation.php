<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\ContentTranslation as BaseContentTranslation;

#[Entity]
#[Table(name: 'app_content_translation')]
class ContentTranslation extends BaseContentTranslation
{
}
