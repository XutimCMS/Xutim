<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xutim\RedirectBundle\DependencyInjection\RedirectResolverPass;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
class XutimRedirectBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RedirectResolverPass());
    }
}
