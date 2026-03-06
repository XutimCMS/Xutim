<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Article;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;

final class ArticleTranslationLocalesDto
{
    /** @param list<string> $translationLocales */
    public function __construct(
        public bool $allTranslationLocales,
        public array $translationLocales,
    ) {
    }

    public static function fromArticle(ArticleInterface $article): self
    {
        return new self($article->hasAllTranslationLocales(), $article->getTranslationLocales());
    }
}
