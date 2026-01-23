<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\EditorBundle\Domain\Factory\ContentBlockFactory;
use Xutim\EditorBundle\Domain\Factory\ContentDraftFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ContentDraftFactory::class)
        ->arg('$entityClass', '%xutim_editor.model.content_draft.class%');

    $services->set(ContentBlockFactory::class)
        ->arg('$blockTypes', '%xutim_editor.block_types%');
};
