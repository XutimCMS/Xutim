<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Model\Coordinates;

class BlockItemDto
{
    public function __construct(
        public ?Page $page,
        public ?Article $article,
        public null|UploadedFile|File $file,
        public ?Snippet $snippet,
        public ?int $position,
        public ?string $link,
        public ?string $color,
        public ?string $fileDescription,
        public ?Coordinates $coordinates
    ) {
    }
}
