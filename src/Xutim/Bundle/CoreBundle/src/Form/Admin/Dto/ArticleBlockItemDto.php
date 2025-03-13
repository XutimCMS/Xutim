<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Model\Coordinates;

class ArticleBlockItemDto
{
    public function __construct(
        public Article $article,
        public null|UploadedFile|File $file,
        public ?Snippet $snippet,
        public ?int $position,
        public ?string $link,
        public Color $color,
        public ?string $fileDescription,
        public ?Coordinates $coordinates
    ) {
    }

    public function toBlockItemDto(): BlockItemDto
    {
        return new BlockItemDto(null, $this->article, $this->file, $this->snippet, $this->position, $this->link, $this->color->getHex(), $this->fileDescription, $this->coordinates);
    }
}
