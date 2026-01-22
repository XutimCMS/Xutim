<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\MediaFolder as BaseMediaFolder;

#[Entity]
#[Table(name: 'app_media_folder')]
class MediaFolder extends BaseMediaFolder
{
}
