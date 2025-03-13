<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\RouteGuesserExtensionRuntime;

class RouteGuesserExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('guess_route', [RouteGuesserExtensionRuntime::class, 'guessRoute']),
            new TwigFunction('switch_locale_route', [RouteGuesserExtensionRuntime::class, 'switchLocaleRoute']),
        ];
    }
}
