<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\SecurityBundle\Factory\ResetPasswordRequestFactory;
use Xutim\SecurityBundle\Factory\UserFactory;
use Xutim\SecurityComponent\Domain\Factory\ResetPasswordRequestFactoryInterface;
use Xutim\SecurityComponent\Domain\Factory\UserFactoryInterface;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(UserFactory::class)
        ->arg('$entityClass', '%xutim_security.model.user.class%');

    $services->set(ResetPasswordRequestFactory::class)
        ->arg('$entityClass', '%xutim_security.model.reset_password_request.class%');

    $services->alias(UserFactoryInterface::class, UserFactory::class);
    $services->alias(ResetPasswordRequestFactoryInterface::class, ResetPasswordRequestFactory::class);
};
