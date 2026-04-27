<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Asset\Context\RequestStackContext;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolver;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolverInterface;
use Xutim\CoreBundle\Dashboard\TranslationStatProvider;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderInterface;
use Xutim\CoreBundle\Infra\Doctrine\Type\AbstractEnumType;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\MessageHandler\EventHandlerInterface;
use Xutim\CoreBundle\Twig\Extension\AnalyticsExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(EventHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(AbstractEnumType::class)
        ->tag('xutim.doctrine_enum_type');

    $services->instanceof(BlockItemProviderInterface::class)
        ->tag('xutim.block_item_provider');

    $services->instanceof(LayoutDefinition::class)
        ->tag('xutim.layout_definition');

    $services->instanceof(AdminEditUrlResolverInterface::class)
        ->tag('xutim.admin_edit_url_resolver');

    $services->instanceof(TranslationStatProvider::class)
        ->tag('xutim.translation_stat_provider');

    $services->alias(RequestStackContext::class, 'assets.context');

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('string $sitemapFile', '%kernel.project_dir%/public/sitemap.xml')
        ->bind('string $publicUploadsDirectory', '/media/uploads/')
        ->bind('string $templatesDir', '%kernel.project_dir%/templates')
        ->bind('string $themesRelativeDir', 'themes')
        ->bind('string $articleLayoutRelativeDir', 'layout/article')
        ->bind('string $pageLayoutRelativeDir', 'layout/page')
        ->bind('string $blockLayoutRelativeDir', 'layout/block')
        ->bind('string $tagLayoutRelativeDir', 'layout/tag')
        ->bind('string $defaultLocale', '%kernel.default_locale%')
        ->bind('string $appHost', '%env(APP_HOST)%')
    ;

    $services->load('Xutim\\CoreBundle\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');

    $services->set(LayoutDefinitionRegistry::class)
        ->arg('$definitions', tagged_iterator('xutim.layout_definition'));

    $services->set(AdminEditUrlResolver::class)
        ->arg('$resolvers', tagged_iterator('xutim.admin_edit_url_resolver'));

    $services->set(AnalyticsExtension::class)
        ->arg('$bundles', '%kernel.bundles%')
        ->tag('twig.extension');
};
