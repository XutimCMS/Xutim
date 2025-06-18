<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Xutim\RedirectComponent\Infra\RedirectResolver;

class RedirectResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(RedirectResolver::class)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('redirect.target_resolver');

        $references = [];
        foreach ($taggedServices as $id => $tags) {
            $references[] = new Reference($id);
        }

        $container->getDefinition(RedirectResolver::class)
            ->setArgument(0, $references);
    }
}
