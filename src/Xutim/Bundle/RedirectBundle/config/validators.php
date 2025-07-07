<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Infra\Validator\ValidRedirect;
use Xutim\RedirectBundle\Infra\Validator\ValidRedirectValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ValidRedirectValidator::class)
        ->arg('$repo', service(RedirectRepositoryInterface::class))
        ->tag('validator.constraint_validator', ['alias' => ValidRedirect::class])
    ;
};
