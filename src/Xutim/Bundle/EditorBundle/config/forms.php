<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Form\CodeBlockType;
use Xutim\EditorBundle\Form\EmbedBlockType;
use Xutim\EditorBundle\Form\HeadingBlockType;
use Xutim\EditorBundle\Form\ImageBlockType;
use Xutim\EditorBundle\Form\LayoutBlockType;
use Xutim\EditorBundle\Form\ListItemBlockType;
use Xutim\EditorBundle\Form\ParagraphBlockType;
use Xutim\EditorBundle\Form\QuoteBlockType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ParagraphBlockType::class)
        ->tag('form.type');

    $services->set(HeadingBlockType::class)
        ->tag('form.type');

    $services->set(ListItemBlockType::class)
        ->tag('form.type');

    $services->set(QuoteBlockType::class)
        ->tag('form.type');

    $services->set(ImageBlockType::class)
        ->tag('form.type');

    $services->set(EmbedBlockType::class)
        ->tag('form.type');

    $services->set(CodeBlockType::class)
        ->tag('form.type');

    $services->set(LayoutBlockType::class)
        ->arg('$templateRegistry', service(BlockTemplateRegistry::class))
        ->tag('form.type');
};
