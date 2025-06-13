<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\TagExtensionRuntime;

class TagExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fetch_tags', [TagExtensionRuntime::class, 'fetchTags']),
            new TwigFunction('fetch_tag', [TagExtensionRuntime::class, 'fetchTag']),
            new TwigFunction('get_tag_layout', [TagExtensionRuntime::class, 'getTagLayout']),
        ];
    }
}
