<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Xutim\SecurityBundle\Security\Voter\CanTranslateVoter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CanTranslateVoter::class)
        ->arg('$accessDecisionManager', service(AccessDecisionManagerInterface::class))
        ->tag('security.voter');
};
