<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Article;

use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;

final readonly class ArticleDto
{
    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
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
        public Page $page
    ) {
    }

    public static function fromArticle(Article $article): self
    {
        $translation = $article->getDefaultTranslation();

        return new self(
            $article->getLayout(),
            $translation->getPreTitle(),
            $translation->getTitle(),
            $translation->getSubTitle(),
            $translation->getSlug(),
            $translation->getContent(),
            $translation->getDescription(),
            $translation->getLocale(),
            $article->getPage()
        );
    }
}
