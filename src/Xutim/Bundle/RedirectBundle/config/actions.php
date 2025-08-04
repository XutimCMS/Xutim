<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\RedirectBundle\Action\Admin\CreateRedirectAction;
use Xutim\RedirectBundle\Action\Admin\DeleteRedirectAction;
use Xutim\RedirectBundle\Action\Admin\EditRedirectAction;
use Xutim\RedirectBundle\Action\Admin\ListRedirectsAction;
use Xutim\RedirectBundle\Action\Admin\RedirectToTargetAction;
use Xutim\RedirectBundle\Domain\Factory\RedirectFactoryInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Infra\Routing\RedirectRouteService;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CreateRedirectAction::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->arg('$factory', service(RedirectFactoryInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$redirectRouteService', service(RedirectRouteService::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(EditRedirectAction::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$redirectRouteService', service(RedirectRouteService::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(ListRedirectsAction::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->arg('$filterBuilder', service(ListFilterBuilder::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(DeleteRedirectAction::class)
        ->arg('$csrfTokenChecker', service(CsrfTokenChecker::class))
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(RedirectToTargetAction::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->tag('controller.service_arguments')
    ;
};
