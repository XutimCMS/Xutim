<?php

declare(strict_types=1);

namespace App\Entity\Snippet;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\SnippetBundle\Domain\Model\Snippet as BaseSnippet;

#[Entity]
#[Table(name: 'app_snippet')]
class Snippet extends BaseSnippet
{
}
