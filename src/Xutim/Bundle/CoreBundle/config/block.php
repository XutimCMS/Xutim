<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Contract\Block\BlockRendererInterface;
use Xutim\CoreBundle\Infra\Block\TwigBlockRenderer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(TwigBlockRenderer::class)
        ->autowire()
        ->autoconfigure()
    ;

    $services
        ->alias(BlockRendererInterface::class, TwigBlockRenderer::class)
    ;
};
