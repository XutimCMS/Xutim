<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Article;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleTranslationLocalesDto;

final readonly class EditArticleTranslationLocalesCommand
{
    /** @param list<string> $translationLocales */
    public function __construct(
        public Uuid $articleId,
        public bool $allTranslationLocales,
        public array $translationLocales,
        public string $userIdentifier,
    ) {
    }

    public static function fromDto(ArticleTranslationLocalesDto $dto, Uuid $articleId, string $userIdentifier): self
    {
        return new self($articleId, $dto->allTranslationLocales, $dto->translationLocales, $userIdentifier);
    }
}
