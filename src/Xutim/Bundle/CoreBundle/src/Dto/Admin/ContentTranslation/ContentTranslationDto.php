<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\ContentTranslation;

use Xutim\CoreBundle\Entity\ContentTranslation;

final readonly class ContentTranslationDto
{
    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function __construct(
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public string $locale
    ) {
    }

    public static function fromTranslation(ContentTranslation $translation): self
    {
        return new self(
            $translation->getPreTitle(),
            $translation->getTitle(),
            $translation->getSubTitle(),
            $translation->getSlug(),
            $translation->getContent(),
            $translation->getDescription(),
            $translation->getLocale()
        );
    }
}
