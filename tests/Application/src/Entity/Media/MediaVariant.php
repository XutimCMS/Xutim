<?php

declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\MediaBundle\Domain\Model\MediaVariant as BaseMediaVariant;

#[Entity]
#[Table(name: 'app_media_variant')]
class MediaVariant extends BaseMediaVariant
{
}
