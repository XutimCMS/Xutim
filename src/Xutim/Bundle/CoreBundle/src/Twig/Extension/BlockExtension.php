<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\BlockExtensionRuntime;

class BlockExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fetch_block_codes', [BlockExtensionRuntime::class, 'fetchCodes']),
        ];
    }
}
