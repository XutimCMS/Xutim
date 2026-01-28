<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\DependencyInjection;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class XutimEditorExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>, block_types: array<string, class-string>} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_editor.model.%s.class', $alias), $modelConfig['class']);
        }

        $container->setParameter('xutim_editor.block_types', $configs['block_types']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
        $loader->load('repositories.php');
        $loader->load('factories.php');
        $loader->load('forms.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Xutim\EditorBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);

        $bundleConfigs = $container->getExtensionConfig($this->getAlias());
        /** @var array{models: array<string, array{class: class-string}>, block_types: array<string, class-string>} $config */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $mapping = [];
        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\EditorBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);

        $this->prependAssetMapper($container);
    }

    private function prependAssetMapper(ContainerBuilder $container): void
    {
        if (!$this->isAssetMapperAvailable($container)) {
            return;
        }

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    __DIR__ . '/../../assets' => '@xutim/editor-bundle',
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
