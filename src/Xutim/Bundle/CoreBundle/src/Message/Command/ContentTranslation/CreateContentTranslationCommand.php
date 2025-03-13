<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\ContentTranslation;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;

final readonly class CreateContentTranslationCommand
{
    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        public ?Uuid $pageId,
        public ?Uuid $articleId,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $locale,
        public string $userIdentifier
    ) {
    }

    public static function fromDto(
        ContentTranslationDto $dto,
        ?Uuid $pageId,
        ?Uuid $articleId,
        string $userIdentifier
    ): self {
        return new self(
            $pageId,
            $articleId,
            $dto->preTitle,
            $dto->title,
            $dto->subTitle,
            $dto->slug,
            $dto->content,
            $dto->description,
            $dto->locale,
            $userIdentifier
        );
    }

    public function hasArticle(): bool
    {
        return $this->articleId !== null;
    }

    public function hasPage(): bool
    {
        return $this->pageId !== null;
    }
}
