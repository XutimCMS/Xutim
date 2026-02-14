<?php

declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\MediaBundle\Domain\Model\MediaFolder as BaseMediaFolder;

#[Entity]
#[Table(name: 'app_media_library_folder')]
class MediaFolder extends BaseMediaFolder
{
}
