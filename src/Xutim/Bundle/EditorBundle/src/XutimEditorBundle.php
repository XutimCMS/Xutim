<?php

declare(strict_types=1);

namespace Xutim\EditorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xutim\EditorBundle\DependencyInjection\Compiler\ContentBlockDiscriminatorMapPass;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class XutimEditorBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ContentBlockDiscriminatorMapPass());
    }
}
