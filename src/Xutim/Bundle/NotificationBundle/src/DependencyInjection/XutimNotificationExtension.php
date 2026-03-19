<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Xutim\NotificationBundle\Message\Notification\SendNotificationMessage;

final class XutimNotificationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_notification.model.%s.class', $alias), $modelConfig['class']);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
        $loader->load('repositories.php');
        $loader->load('factories.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundleConfigs = $container->getExtensionConfig($this->getAlias());

        /**
         * @var array{
         *      models: array<string, array{class: class-string}>,
         *      message_routing?: array<class-string, string>
         * } $config
         */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('twig.php');

        $this->prependDoctrineResolveTargets($container, $config);
        $this->prependMessengerRouting($container, $config);
    }

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependDoctrineResolveTargets(ContainerBuilder $container, array $config): void
    {
        $mapping = [];

        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\NotificationBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);
    }

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependMessengerRouting(ContainerBuilder $container, array $config): void
    {
        $messagesToRoute = [
            SendNotificationMessage::class,
        ];

        $routing = [];

        foreach ($messagesToRoute as $messageClass) {
            if (!class_exists($messageClass)) {
                continue;
            }

            $routing[$messageClass] = $config['message_routing'][$messageClass] ?? 'async';
        }

        if ($routing !== []) {
            $container->prependExtensionConfig('framework', [
                'messenger' => ['routing' => $routing],
            ]);
        }
    }
}
