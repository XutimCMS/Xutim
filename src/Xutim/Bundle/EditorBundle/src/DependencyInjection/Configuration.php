<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xutim_editor');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('models')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                                ->info('The FQCN of the concrete entity class.')
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
                ->arrayNode('block_types')
                    ->info('Custom block type registration for STI discriminator map')
                    ->useAttributeAsKey('discriminator')
                    ->prototype('scalar')
                        ->info('Block class FQCN')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
