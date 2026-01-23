<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Infra\Doctrine\ContentBlockDiscriminatorMapSubscriber;
use Xutim\EditorBundle\Service\ContentBlockRenderer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(BlockTemplateInterface::class)
        ->tag('xutim.block_template');

    $services->set(BlockTemplateRegistry::class)
        ->arg('$templates', tagged_iterator('xutim.block_template'));

    $services->set(ContentBlockRenderer::class)
        ->arg('$twig', service('twig'))
        ->arg('$templateRegistry', service(BlockTemplateRegistry::class))
        ->arg('$blockRepository', service(\Xutim\EditorBundle\Repository\ContentBlockRepository::class));

    $services->set(ContentBlockDiscriminatorMapSubscriber::class)
        ->arg('$blockTypes', '%xutim_editor.block_types%');
};
