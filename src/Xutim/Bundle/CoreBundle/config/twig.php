<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('twig', [
        'globals' => [
            'xutim_core_filter_sets' => '%xutim_core.filter_sets%',
            'xutim_core_admin_filter_sets' => '%xutim_core.admin_filter_sets%',
        ],
    ]);
};
