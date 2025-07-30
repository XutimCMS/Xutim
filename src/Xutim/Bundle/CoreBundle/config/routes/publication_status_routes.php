<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Xutim\CoreBundle\Action\Admin\PublicationStatus\ChangeStatusAction;
use Xutim\CoreBundle\Action\Admin\PublicationStatus\ChangeTagStatusAction;
use Xutim\CoreBundle\Entity\PublicationStatus;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_publication_status_edit', '/admin/{_content_locale}/publication-status/edit/{id}/{status}')
        ->methods(['post'])
        ->requirements(['status' => new EnumRequirement(PublicationStatus::class)])
        ->controller(ChangeStatusAction::class)
    ;

    $routes
        ->add('admin_tag_publication_status_edit', '/admin/{_content_locale}/publication-status/tag/edit/{id}/{status}')
        ->methods(['post'])
        ->requirements(['status' => new EnumRequirement(PublicationStatus::class)])
        ->controller(ChangeTagStatusAction::class)

    ;
};
