<?php

declare(strict_types=1);

namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\FileTranslation as BaseFileTranslation;

#[Entity]
#[Table(name: 'app_file_translation')]
class FileTranslation extends BaseFileTranslation
{
}
