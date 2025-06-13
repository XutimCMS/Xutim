<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xutim_core');

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
            ->arrayNode('filter_sets')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->fixXmlConfig('filter', 'filters')
            ->children()
            ->scalarNode('quality')->end()
            ->scalarNode('jpeg_quality')->end()
            ->scalarNode('png_compression_level')->end()
            ->scalarNode('png_compression_filter')->end()
            ->scalarNode('format')->end()
            ->booleanNode('animated')->end()
            ->scalarNode('cache')->end()
            ->scalarNode('data_loader')->end()
            ->scalarNode('default_image')->end()
            ->arrayNode('filters')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->useAttributeAsKey('name')
            ->ignoreExtraKeys(false)
            ->prototype('variable')->end()
            ->end()
            ->end()
            ->arrayNode('post_processors')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->useAttributeAsKey('name')
            ->ignoreExtraKeys(false)
            ->prototype('variable')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
