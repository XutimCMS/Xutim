<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\MediaBundle\Form\Admin\MediaCopyrightType;
use Xutim\MediaBundle\Form\Admin\MediaFolderType;
use Xutim\MediaBundle\Form\Admin\MediaTranslationType;
use Xutim\MediaBundle\Form\Admin\UploadMediaType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MediaTranslationType::class)
        ->tag('form.type');

    $services->set(UploadMediaType::class)
        ->args([
            service(SiteContext::class),
        ])
        ->tag('form.type');

    $services->set(MediaFolderType::class)
        ->tag('form.type');

    $services->set(MediaCopyrightType::class)
        ->tag('form.type');
};
