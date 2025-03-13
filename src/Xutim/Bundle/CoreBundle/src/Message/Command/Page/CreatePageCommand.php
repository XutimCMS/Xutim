<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\Page\PageDto;

final readonly class CreatePageCommand
{
    /**
     * @param list<string> $locales
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        public ?string $layout,
        public ?string $color,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public array $locales,
        public string $defaultLanguage,
        public ?Uuid $parentId,
        public string $userIdentifier
    ) {
    }

    public static function fromDto(PageDto $dto, string $userIdentifier): CreatePageCommand
    {
        return new self(
            $dto->layout,
            $dto->color,
            $dto->preTitle,
            $dto->title,
            $dto->subTitle,
            $dto->slug,
            $dto->content,
            $dto->description,
            $dto->locales,
            $dto->locale,
            $dto->parent?->getId(),
            $userIdentifier
        );
    }
}
