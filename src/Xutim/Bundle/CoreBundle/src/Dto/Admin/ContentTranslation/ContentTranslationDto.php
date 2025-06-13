<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\ContentTranslation;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

final readonly class ContentTranslationDto
{
    /**
     * @param EditorBlock $content
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

    public static function fromTranslation(ContentTranslationInterface $translation): self
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
