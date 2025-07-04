<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Validator\UniqueUsernameValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UniqueUsernameValidator::class)
        ->arg('$repo', service(UserRepositoryInterface::class))
        ->tag('validator.constraint_validator');
};
