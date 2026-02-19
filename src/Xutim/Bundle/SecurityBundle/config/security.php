<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Xutim\SecurityBundle\EventSubscriber\AdminContentLocaleRedirectSubscriber;
use Xutim\SecurityBundle\EventSubscriber\OnlineUserSubscriber;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserLoginAuthenticator;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UserLoginAuthenticator::class)
        ->arg('$urlGenerator', service(UrlGeneratorInterface::class))
    ;

    $services->set(CsrfTokenChecker::class)
        ->arg('$csrfTokenManager', service(CsrfTokenManagerInterface::class))
    ;

    $services->set(AdminContentLocaleRedirectSubscriber::class)
        ->arg('$translatorAuthChecker', service(TranslatorAuthChecker::class))
        ->arg('$userStorage', service(UserStorage::class))
        ->arg('$router', service(RouterInterface::class))

        ->tag('kernel.event_subscriber')
    ;

    $services->set(OnlineUserSubscriber::class)
        ->arg('$userStorage', service(UserStorage::class))
        ->arg('$onlineUsersCache', service(CacheInterface::class))

        ->tag('kernel.event_subscriber')

    ;
};
