<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// use Doctrine\Persistence\ManagerRegistry;
// use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;
// use Xutim\EventBundle\Infra\Doctrine\ORM\EventTranslationRepository;

return static function (ContainerConfigurator $container): void {
    // $services = $container->services();
    // $services->set(EventRepository::class)
    //     ->arg('$registry', service(ManagerRegistry::class))
    //     ->arg('$entityClass', '%xutim_event.model.event.class%')
    //     ->tag('doctrine.repository_service');
    //
    // $services->set(EventTranslationRepository::class)
    //     ->arg('$registry', service(ManagerRegistry::class))
    //     ->arg('$entityClass', '%xutim_event.model.event_translation.class%')
    //     ->tag('doctrine.repository_service');
};
