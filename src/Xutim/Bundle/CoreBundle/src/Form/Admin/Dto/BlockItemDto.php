<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

class BlockItemDto
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public ?PageInterface $page = null,
        public ?ArticleInterface $article = null,
        public ?MediaInterface $file = null,
        public ?SnippetInterface $snippet = null,
        public ?TagInterface $tag = null,
        public ?MediaFolderInterface $mediaFolder = null,
        public ?int $position = null,
        public ?string $text = null,
        public ?string $link = null,
        public ?string $fileDescription = null,
        public array $extra = [],
    ) {
    }
}
