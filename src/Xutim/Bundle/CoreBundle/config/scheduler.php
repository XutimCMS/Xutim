<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Reference;
use Xutim\CoreBundle\Scheduled\DispatchPublishScheduledArticlesTask;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // $services
    //     ->set(DispatchPublishScheduledArticlesTask::class)
    //     ->arg('$bus', new Reference('messenger.default_bus'))
    //     ->tag('scheduler.task', [
    //         'trigger' => 'every',
    //         'frequency' => 60,
    //         'task' => 'publish_scheduled_articles',
    //     ]);
};
