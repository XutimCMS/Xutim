<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

class BlockItemDto
{
    public function __construct(
        public ?PageInterface $page,
        public ?ArticleInterface $article,
        public ?MediaInterface $file,
        public ?SnippetInterface $snippet,
        public ?TagInterface $tag,
        public ?MediaFolderInterface $mediaFolder,
        public ?int $position,
        public ?string $text,
        public ?string $link,
        public ?string $color,
        public ?string $fileDescription,
        public ?Coordinates $coordinates
    ) {
    }
}
