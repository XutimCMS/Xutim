<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xutim_snippet');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('models')
            ->useAttributeAsKey('alias')
            ->prototype('array')
            ->children()
            ->scalarNode('class')
            ->info('The FQCN of the concrete entity class used by the application, extending the bundle\'s base entity.')
            ->isRequired()
            ->cannotBeEmpty()
            ->validate()
            ->ifTrue(fn (string $v) => !class_exists($v))
            ->thenInvalid('The class "%s" does not exist.')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
