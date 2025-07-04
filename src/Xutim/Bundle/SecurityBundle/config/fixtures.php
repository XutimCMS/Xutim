<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\DataFixtures\LoadUserFixture;
use Xutim\SecurityComponent\Domain\Factory\UserFactoryInterface;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(LoadUserFixture::class)
        ->arg('$userFactory', service(UserFactoryInterface::class))
        ->tag('doctrine.fixture.orm');
};
