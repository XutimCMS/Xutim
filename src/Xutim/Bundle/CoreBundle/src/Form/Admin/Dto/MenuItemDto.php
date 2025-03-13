<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;

final readonly class MenuItemDto
{
    public function __construct(
        public bool $hasLink,
        public ?Page $page,
        public ?Article $article
    ) {
    }
}
