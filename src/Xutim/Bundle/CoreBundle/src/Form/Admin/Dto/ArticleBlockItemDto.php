<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\Color;

class ArticleBlockItemDto
{
    public function __construct(
        public ArticleInterface $article,
        public ?FileInterface $file,
        public ?SnippetInterface $snippet,
        public ?TagInterface $tag,
        public ?int $position,
        public ?string $link,
        public Color $color,
        public ?string $fileDescription,
        public ?Coordinates $coordinates
    ) {
    }

    public function toBlockItemDto(): BlockItemDto
    {
        return new BlockItemDto(null, $this->article, $this->file, $this->snippet, $this->tag, $this->position, $this->link, $this->color->getHex(), $this->fileDescription, $this->coordinates);
    }
}
