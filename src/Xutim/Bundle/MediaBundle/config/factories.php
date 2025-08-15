<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Xutim\EventBundle\Domain\Factory\EventFactory;
// use Xutim\EventBundle\Domain\Factory\EventTranslationFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // $services->set(EventFactory::class)
    //     ->arg('$eventClass', '%xutim_event.model.event.class%')
    //     ->arg('$eventTranslationClass', '%xutim_event.model.event_translation.class%');
    //
    // $services->set(EventTranslationFactory::class)
    //     ->arg('$eventTranslationClass', '%xutim_event.model.event_translation.class%');
};
