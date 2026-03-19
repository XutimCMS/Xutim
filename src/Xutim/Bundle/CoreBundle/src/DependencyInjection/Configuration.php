<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Entity\BlockItem;
use Xutim\CoreBundle\Entity\ContentDraft;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\LogEvent;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\Site;
use Xutim\CoreBundle\Entity\Tag;
use Xutim\CoreBundle\Entity\TagTranslation;

final class Configuration implements ConfigurationInterface
{
    private const DEFAULT_MODELS = [
        'block_item' => BlockItem::class,
        'block' => Block::class,
        'tag' => Tag::class,
        'content_translation' => ContentTranslation::class,
        'log_event' => LogEvent::class,
        'article' => Article::class,
        'page' => Page::class,
        'menu_item' => MenuItem::class,
        'site' => Site::class,
        'tag_translation' => TagTranslation::class,
        'content_draft' => ContentDraft::class,
    ];

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
                    ->defaultValue(array_map(
                        static fn (string $class): array => ['class' => $class],
                        self::DEFAULT_MODELS
                    ))
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

                ->arrayNode('message_routing')
                    ->info('Override routing of Messenger messages defined in this bundle')
                    ->useAttributeAsKey('message_class')
                    ->scalarPrototype()->defaultValue('async')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
