<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Tag;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Form\Admin\Dto\TagDto;

final readonly class CreateTagCommand
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $defaultLanguage,
        public Color $color,
        public ?Uuid $featuredImageId,
        public string $userIdentifier,
        public ?string $layout
    ) {
    }

    public static function fromDto(TagDto $dto, string $userIdentifier): self
    {
        return new self(
            $dto->name,
            $dto->slug,
            $dto->locale,
            $dto->color,
            $dto->featuredImageId,
            $userIdentifier,
            $dto->layout
        );
    }

    /**
     * @phpstan-assert-if-true Uuid $this->featuredImageId
     * @phpstan-assert-if-false null $this->featuredImageId
     */
    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }
}
