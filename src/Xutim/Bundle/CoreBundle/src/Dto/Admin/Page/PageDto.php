<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Xutim\CoreBundle\Entity\Page;

final readonly class PageDto
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
        public string $locale,
        public ?Page $parent
    ) {
    }

    public static function fromPage(Page $page): self
    {
        $translation = $page->getDefaultTranslation();
        return new self(
            $page->getLayout(),
            $page->getColor()->getHex(),
            $translation->getPreTitle(),
            $translation->getTitle(),
            $translation->getSubTitle(),
            $translation->getSlug(),
            $translation->getContent(),
            $translation->getDescription(),
            $page->getLocales(),
            $translation->getLocale(),
            $page->getParent()
        );
    }
}
