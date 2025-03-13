<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Infra\Doctrine\Type\AbstractEnumType;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\MessageHandler\EventHandlerInterface;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('xutim_locales', [
            'ar', 'be', 'bg', 'cs', 'zh', 'da', 'de', 'et',
            'el', 'en', 'es', 'ca', 'fr', 'fi', 'sw', 'hr',
            'id', 'it', 'ja', 'ko', 'lv', 'lt', 'hu', 'nl',
            'no', 'pl', 'pt', 'ro', 'ru', 'sr', 'sk', 'sl',
            'sv', 'ta', 'uk', 'vi'
        ]);
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(EventHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(AbstractEnumType::class)
        ->tag('xutim.doctrine_enum_type');

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('array $locales', [
            'ar', 'be', 'bg', 'cs', 'zh', 'da', 'de', 'et',
            'el', 'en', 'es', 'ca', 'fr', 'fi', 'sw', 'hr',
            'id', 'it', 'ja', 'ko', 'lv', 'lt', 'hu', 'nl',
            'no', 'pl', 'pt', 'ro', 'ru', 'sr', 'sk', 'sl',
            'sv', 'ta', 'uk', 'vi'
        ])
        ->bind('string $filesDirectory', '%kernel.project_dir%/var/uploads/files/')
        ->bind('string $templatesDir', '%kernel.project_dir%/templates')
        ->bind('string $themesRelativeDir', 'themes')
        ->bind('string $articleLayoutRelativeDir', 'layout/article')
        ->bind('string $pageLayoutRelativeDir', 'layout/page')
        ->bind('string $blockLayoutRelativeDir', 'layout/block')
    ;

    $services->load('Xutim\\CoreBundle\\', '../src/')
            ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
};
