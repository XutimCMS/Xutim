<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()->set('xutim_core.admin_filter_sets', [
        'thumb_small' => [
            'quality' => 75,
            'filters' => [
                'thumbnail' => [
                    'size' => [227, 227],
                    'mode' => 'outbound',
                    'position' => 'center',
                    'background' => ['color' => '#ffffff'],
                    'sharpen' => null,
                ],
            ],
        ],
        'thumb_large' => [
            'quality' => 75,
            'filters' => [
                'thumbnail' => [
                    'size' => [300, 300],
                    'mode' => 'outbound',
                    'position' => 'center',
                    'background' => ['color' => '#ffffff'],
                    'sharpen' => null,
                ],
            ],
        ],
    ]);
};
