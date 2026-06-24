<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\PageInterface;

final readonly class PageDto
{
    /**
     * @param list<string> $locales
     * @param EditorBlock  $content
     */
    public function __construct(
        public ?string $layout,
        public string $preTitle,
        public string $title,
        public string $subTitle,
        public string $slug,
        public array $content,
        public string $description,
        public ?bool $allTranslationLocales,
        public array $locales,
        public string $locale,
        public ?PageInterface $parent,
        public ?Uuid $featuredImageId
    ) {
    }
}
