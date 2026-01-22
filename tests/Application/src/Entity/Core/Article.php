<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\Article as BaseArticle;

#[Entity]
#[Table(name: 'app_article')]
class Article extends BaseArticle
{
}
