<?php

declare(strict_types=1);

namespace App\Entity\Snippet;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslation as BaseSnippetTranslation;

#[Entity]
#[Table(name: 'app_snippet_translation')]
class SnippetTranslation extends BaseSnippetTranslation
{
}
