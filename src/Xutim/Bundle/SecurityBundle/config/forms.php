<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SecurityBundle\Form\ChangePasswordFormType;
use Xutim\SecurityBundle\Form\CreateUserType;
use Xutim\SecurityBundle\Form\EditUserType;
use Xutim\SecurityBundle\Form\UserChangePasswordType;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CreateUserType::class)
        ->arg('$siteContext', service(SiteContext::class))
        ->tag('form.type');

    $services->set(EditUserType::class)
        ->arg('$siteContext', service(SiteContext::class))
        ->tag('form.type');

    $services->set(UserChangePasswordType::class)
        ->tag('form.type');

    $services->set(ChangePasswordFormType::class)
        ->tag('form.type');
};
