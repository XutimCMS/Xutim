<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\DependencyInjection;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class XutimAnalyticsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>, site_host: string|null} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);

        $container->setParameter('xutim_analytics.site_host', $configs['site_host']);

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_analytics.model.%s.class', $alias), $modelConfig['class']);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('repositories.php');
        $loader->load('factories.php');
        $loader->load('handlers.php');
        $loader->load('actions.php');
        $loader->load('services.php');

        // if ($container->getParameter('kernel.environment') === 'test') {
        //     $loader->load('fixtures.php');
        // }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Xutim\AnalyticsBundle\Migrations' => __DIR__ . '/../Migrations',
            ]
        ]);

        $bundleConfigs = $container->getExtensionConfig($this->getAlias());
        /** @var array{
         *      models: array<string, array{class: class-string}>,
         *      message_routing?: array<class-string, string>
         * } $config */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $mapping = [];
        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\AnalyticsBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);

        $this->prependMessengerRouting($container, $config);
        $this->prependAssetMapper($container, $config);
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
            CollectAnalyticsMessage::class,
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

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependAssetMapper(ContainerBuilder $container, array $config): void
    {
        if (!$this->isAssetMapperAvailable($container)) {
            return;
        }

        $container->prependExtensionConfig('framework', [
              'asset_mapper' => [
                  'paths' => [
                      __DIR__ . '/../../assets' => '@xutim/analytics-bundle',
                  ],
              ],
          ]);
    }

    private function isAssetMapperAvailable(ContainerBuilder $container): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }
        /** @var array<string> $frameworkConfig */
        $frameworkConfig = $bundlesMetadata['FrameworkBundle'];

        /** @var string $frameworkPath */
        $frameworkPath = $frameworkConfig['path'];

        return is_file($frameworkPath . '/Resources/config/asset_mapper.php');
    }
}
