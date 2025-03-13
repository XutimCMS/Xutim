<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Xutim\CoreBundle\Entity\Page;

final class PageMinimalDto
{
    /**
     * @param list<string> $locales
     */
    public function __construct(
        public ?string $color,
        public array $locales,
        public ?Page $parent
    ) {
    }

    public static function fromPage(Page $page): self
    {
        return new self(
            $page->getColor()->getHex(),
            $page->getLocales(),
            $page->getParent()
        );
    }
}
