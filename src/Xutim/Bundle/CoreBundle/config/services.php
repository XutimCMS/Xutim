<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Asset\Context\RequestStackContext;
use Xutim\CoreBundle\Infra\Doctrine\Type\AbstractEnumType;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\MessageHandler\EventHandlerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(EventHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(AbstractEnumType::class)
        ->tag('xutim.doctrine_enum_type');

    $services->alias(RequestStackContext::class, 'assets.context');

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('string $filesDirectory', '%kernel.project_dir%/public/media/uploads/')
        ->bind('string $sitemapFile', '%kernel.project_dir%/public/sitemap.xml')
        ->bind('string $publicUploadsDirectory', '/media/uploads/')
        ->bind('string $templatesDir', '%kernel.project_dir%/templates')
        ->bind('string $themesRelativeDir', 'themes')
        ->bind('string $articleLayoutRelativeDir', 'layout/article')
        ->bind('string $pageLayoutRelativeDir', 'layout/page')
        ->bind('string $blockLayoutRelativeDir', 'layout/block')
        ->bind('string $tagLayoutRelativeDir', 'layout/tag')
        ->bind('string $defaultLocale', '%kernel.default_locale%')
    ;

    $services->load('Xutim\\CoreBundle\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
    $services->alias(FilterService::class, 'liip_imagine.service.filter');
};
