<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Xutim\CoreBundle\Entity\ContentTranslation as BaseContentTranslation;

#[Entity]
#[Table(name: 'app_content_translation')]
#[UniqueConstraint(columns: ['slug', 'locale'])]
#[UniqueConstraint(columns: ['article_id', 'locale'])]
#[UniqueConstraint(columns: ['page_id', 'locale'])]
class ContentTranslation extends BaseContentTranslation
{
}
