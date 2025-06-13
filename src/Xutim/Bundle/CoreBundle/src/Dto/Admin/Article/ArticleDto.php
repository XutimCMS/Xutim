<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Article;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Article;

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
            $article->getFeaturedImage()?->getId()
        );
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }
}
