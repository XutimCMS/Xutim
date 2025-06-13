<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\ContentLocaleExtensionRuntime;

class ContentLocaleExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_extended_locale', [ContentLocaleExtensionRuntime::class, 'isExtendedLocale']),
        ];
    }
}
