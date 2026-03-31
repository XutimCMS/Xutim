<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Canonical;

final readonly class CanonicalBlock
{
    /**
     * @param array<string, mixed>           $attrs
     * @param list<InlineRun>                $body
     * @param array<string, list<InlineRun>> $parts
     * @param list<CanonicalBlock>           $children
     * @param list<ListItem>                 $listItems
     * @param list<GalleryImage>             $galleryImages
     * @param array<string, mixed>|null      $fallbackRaw
     */
    public function __construct(
        public string $kind,
        public ?string $sourceKey = null,
        public array $attrs = [],
        public array $body = [],
        public array $parts = [],
        public array $children = [],
        public array $listItems = [],
        public array $galleryImages = [],
        public ?array $fallbackRaw = null,
    ) {
    }
}
