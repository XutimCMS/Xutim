<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Article;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleDto;

final readonly class CreateArticleCommand
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
        public string $defaultLanguage,
        public ?Uuid $pageId,
        public string $userIdentifier
    ) {
    }

    public static function fromDto(ArticleDto $dto, string $userIdentifier): self
    {
        return new self(
            $dto->layout,
            $dto->preTitle,
            $dto->title,
            $dto->subTitle,
            $dto->slug,
            $dto->content,
            $dto->description,
            $dto->locale,
            $dto->page->getId(),
            $userIdentifier
        );
    }
}
