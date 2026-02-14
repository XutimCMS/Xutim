<?php

declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\MediaBundle\Domain\Model\MediaTranslation as BaseMediaTranslation;

#[Entity]
#[Table(name: 'app_media_translation')]
class MediaTranslation extends BaseMediaTranslation
{
}
