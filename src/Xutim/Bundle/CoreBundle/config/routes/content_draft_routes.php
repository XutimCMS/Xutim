<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Xutim\CoreBundle\Action\Admin\ContentDraft\DiscardDraftAction;
use Xutim\CoreBundle\Action\Admin\ContentDraft\PublishDraftAction;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('admin_content_draft_publish', '/admin/{_content_locale}/content-draft/{draftId}/publish')
        ->methods(['post'])
        ->controller(PublishDraftAction::class)
    ;

    $routes
        ->add('admin_content_draft_discard', '/admin/{_content_locale}/content-draft/{draftId}/discard')
        ->methods(['post'])
        ->controller(DiscardDraftAction::class)
    ;
};
