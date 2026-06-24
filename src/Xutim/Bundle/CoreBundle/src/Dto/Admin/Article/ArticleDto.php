<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Article;

use Symfony\Component\Uid\Uuid;

final readonly class ArticleDto
{
    /**
     * @param EditorBlock $content
     */
    public function __construct(
        public ?string $layout,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $locale,
        public ?Uuid $featuredImageId
    ) {
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }
}
