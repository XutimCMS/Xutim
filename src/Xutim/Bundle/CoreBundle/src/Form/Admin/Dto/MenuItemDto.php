<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

final readonly class MenuItemDto
{
    public function __construct(
        public bool $hasLink,
        public ?PageInterface $page,
        public ?ArticleInterface $article,
        public ?TagInterface $tag,
        public ?PageInterface $overwritePage,
        public ?SnippetInterface $snippetAnchor
    ) {
    }
}
