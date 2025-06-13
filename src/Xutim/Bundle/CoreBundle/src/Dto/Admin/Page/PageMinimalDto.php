<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Xutim\CoreBundle\Domain\Model\PageInterface;

final class PageMinimalDto
{
    /**
     * @param list<string> $locales
     */
    public function __construct(
        public ?string $color,
        public array $locales,
        public ?PageInterface $parent
    ) {
    }

    public static function fromPage(PageInterface $page): self
    {
        return new self(
            $page->getColor()->getHex(),
            $page->getLocales(),
            $page->getParent()
        );
    }
}
