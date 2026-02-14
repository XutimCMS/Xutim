<?php

declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\MediaBundle\Domain\Model\Media as BaseMedia;

#[Entity]
#[Table(name: 'app_media')]
class Media extends BaseMedia
{
}
