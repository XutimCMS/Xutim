<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\AdminUrlExtensionRuntime;

class AdminUrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_path', [AdminUrlExtensionRuntime::class, 'generatePath']),
            new TwigFunction('admin_current_path_with', [AdminUrlExtensionRuntime::class, 'getCurrentPathWith']),
            new TwigFunction('admin_filter_order_path_with', [AdminUrlExtensionRuntime::class, 'getFilterOrderPathWith']),
        ];
    }
}
