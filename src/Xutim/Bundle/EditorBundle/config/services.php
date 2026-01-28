<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\EditorBundle\Action\Admin\BlockAddAction;
use Xutim\EditorBundle\Action\Admin\BlockConvertAction;
use Xutim\EditorBundle\Action\Admin\BlockCreateInlineAction;
use Xutim\EditorBundle\Action\Admin\BlockDeleteAction;
use Xutim\EditorBundle\Action\Admin\BlockEditAction;
use Xutim\EditorBundle\Action\Admin\BlockMoveAction;
use Xutim\EditorBundle\Action\Admin\BlockPatchAction;
use Xutim\EditorBundle\Action\Admin\BlockPickerAction;
use Xutim\EditorBundle\Action\Admin\BlockViewAction;
use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Form\BlockFormFactory;
use Xutim\EditorBundle\Infra\Doctrine\ContentBlockDiscriminatorMapSubscriber;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\EditorBundle\Service\ContentBlockRenderer;
use Xutim\EditorBundle\Twig\EditorExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(BlockTemplateInterface::class)
        ->tag('xutim.block_template');

    $services->set(BlockTemplateRegistry::class)
        ->arg('$templates', tagged_iterator('xutim.block_template'));

    $services->set(ContentBlockRenderer::class)
        ->arg('$twig', service('twig'))
        ->arg('$templateRegistry', service(BlockTemplateRegistry::class))
        ->arg('$blockRepository', service(ContentBlockRepository::class));

    $services->set(ContentBlockDiscriminatorMapSubscriber::class)
        ->arg('$blockTypes', '%xutim_editor.block_types%');

    $services->set(BlockFormFactory::class);

    $services->set(EditorExtension::class)
        ->arg('$templateRegistry', service(BlockTemplateRegistry::class))
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->tag('twig.extension');

    $services->set(BlockEditAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$formFactory', service(BlockFormFactory::class))
        ->arg('$symfonyFormFactory', service('form.factory'))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockAddAction::class)
        ->arg('$draftRepository', service(ContentDraftRepository::class))
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$blockFactory', service(\Xutim\EditorBundle\Domain\Factory\ContentBlockFactory::class))
        ->arg('$formFactory', service(BlockFormFactory::class))
        ->arg('$symfonyFormFactory', service('form.factory'))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockMoveAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockDeleteAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockPickerAction::class)
        ->arg('$draftRepository', service(ContentDraftRepository::class))
        ->arg('$templateRegistry', service(BlockTemplateRegistry::class))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockPatchAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockCreateInlineAction::class)
        ->arg('$draftRepository', service(ContentDraftRepository::class))
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$blockFactory', service(\Xutim\EditorBundle\Domain\Factory\ContentBlockFactory::class))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockViewAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');

    $services->set(BlockConvertAction::class)
        ->arg('$blockRepository', service(ContentBlockRepository::class))
        ->arg('$twig', service('twig'))
        ->arg('$security', service('security.helper'))
        ->tag('controller.service_arguments');
};
