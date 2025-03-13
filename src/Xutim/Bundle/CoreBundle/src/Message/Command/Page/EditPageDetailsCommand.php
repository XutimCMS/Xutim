<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Dto\Admin\Page\PageMinimalDto;

final readonly class EditPageDetailsCommand
{
    /**
     * @param list<string> $locales
     */
    public function __construct(
        public Uuid $pageId,
        public ?string $color,
        public array $locales,
        public ?Uuid $parentId,
        public string $userIdentifier
    ) {
    }

    public static function fromDto(PageMinimalDto $dto, Uuid $pageId, string $userIdentifier): self
    {
        return new self(
            $pageId,
            $dto->color,
            $dto->locales,
            $dto->parent?->getId(),
            $userIdentifier
        );
    }
}
