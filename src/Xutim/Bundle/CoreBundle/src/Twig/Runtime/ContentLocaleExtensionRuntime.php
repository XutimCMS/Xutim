<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Context\SiteContext;

class ContentLocaleExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SiteContext $siteContext
    ) {
    }

    public function isExtendedLocale(string $locale): bool
    {
        $found = array_find(
            $this->siteContext->getExtendedContentLocales(),
            fn (string $needle) => $locale === $needle
        );
        if ($found === null) {
            return false;
        }

        return true;
    }
}
