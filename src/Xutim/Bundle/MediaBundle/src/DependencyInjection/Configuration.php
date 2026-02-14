<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Xutim\MediaBundle\Domain\Data\ImagePreset;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xutim_media');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('models')
                    ->useAttributeAsKey('alias')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
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

                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_dir')
                            ->defaultValue('%kernel.project_dir%/public')
                        ->end()
                        ->scalarNode('media_path')
                            ->defaultValue('media')
                        ->end()
                        ->scalarNode('url_prefix')
                            ->defaultValue('/media')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('processing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('driver')
                            ->values(['imagick', 'gd', 'vips'])
                            ->defaultValue('imagick')
                        ->end()
                        ->booleanNode('optimize')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->integerNode('max_width')
                                ->isRequired()
                                ->min(1)
                            ->end()
                            ->integerNode('max_height')
                                ->isRequired()
                                ->min(1)
                            ->end()
                            ->enumNode('fit_mode')
                                ->values(['cover', 'contain', 'scale'])
                                ->defaultValue('cover')
                            ->end()
                            ->arrayNode('quality')
                                ->useAttributeAsKey('format')
                                ->integerPrototype()->min(1)->max(100)->end()
                                ->defaultValue(ImagePreset::DEFAULT_QUALITY)
                            ->end()
                            ->booleanNode('use_focal_point')
                                ->defaultTrue()
                            ->end()
                            ->arrayNode('formats')
                                ->scalarPrototype()->end()
                                ->defaultValue(['avif', 'webp', 'jpg'])
                            ->end()
                            ->arrayNode('responsive_widths')
                                ->integerPrototype()->end()
                                ->defaultValue(ImagePreset::DEFAULT_RESPONSIVE_WIDTHS)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
