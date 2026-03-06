<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\Page;

use Xutim\CoreBundle\Domain\Model\PageInterface;

final class PageTranslationLocalesDto
{
    /** @param list<string> $translationLocales */
    public function __construct(
        public bool $allTranslationLocales,
        public array $translationLocales,
    ) {
    }

    public static function fromPage(PageInterface $page): self
    {
        return new self($page->hasAllTranslationLocales(), $page->getTranslationLocales());
    }
}
