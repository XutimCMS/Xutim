<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\Color;

final readonly class TagDto
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $locale,
        public Color $color,
        public ?Uuid $featuredImageId,
        public ?string $layout
    ) {
    }

    public static function fromTag(TagInterface $tag, string $locale): self
    {
        $translation = $tag->getTranslationByLocale($locale);

        return new self(
            $translation?->getName() ?? '',
            $translation?->getSlug() ?? '',
            $locale,
            $tag->getColor(),
            $tag->getFeaturedImage()?->getId(),
            $tag->getLayout()
        );
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImageId !== null;
    }
}
