<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\VariantPathResolver;
use Xutim\MediaBundle\Twig\Components\Admin\FocalPoint;
use Xutim\MediaBundle\Twig\Components\Admin\Picture;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FocalPoint::class)
        ->args([
            service(StorageAdapterInterface::class),
        ])
        ->tag('twig.component', [
            'key' => 'XutimMedia:Admin:FocalPoint',
            'template' => '@XutimMedia/components/Admin/FocalPoint.html.twig',
        ]);

    $services->set(Picture::class)
        ->args([
            service(VariantPathResolver::class),
            service(MediaVariantRepositoryInterface::class),
        ])
        ->tag('twig.component', [
            'key' => 'XutimMedia:Admin:Picture',
            'template' => '@XutimMedia/components/Admin/Picture.html.twig',
        ]);
};
