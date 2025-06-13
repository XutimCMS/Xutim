<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\Tag;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Color;

final readonly class EditTagCommand
{
    public function __construct(
        public Uuid $tagId,
        public string $name,
        public string $slug,
        public string $locale,
        public Color $color,
        public ?Uuid $featuredImageId,
        public string $userIdentifier,
        public ?string $layout
    ) {
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
