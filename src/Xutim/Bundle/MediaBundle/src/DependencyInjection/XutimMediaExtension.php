<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\DependencyInjection;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Xutim\MediaBundle\Infra\Spatie\SpatieImageProcessor;
use Xutim\MediaBundle\Infra\Storage\LocalStorageAdapter;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;
use Xutim\MediaBundle\Message\RegenerateVariantsMessage;
use Xutim\MediaBundle\Service\BlurHashGenerator;
use Xutim\MediaBundle\Service\ImageProcessorInterface;
use Xutim\MediaBundle\Service\PresetRegistry;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;
use Xutim\MediaBundle\Service\VariantPathResolver;

final class XutimMediaExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        /**
         * @var array{
         *     models: array<string, array{class: class-string}>,
         *     storage: array{public_dir: string, media_path: string, url_prefix: string},
         *     processing: array{driver: string, optimize: bool},
         *     presets: array<string, array{max_width: int, max_height: int, fit_mode: string, quality: array<string, int>, use_focal_point: bool, formats: list<string>, responsive_widths: list<int>}>
         * } $configs
         */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_media.model.%s.class', $alias), $modelConfig['class']);
        }

        $container->setParameter('xutim_media.storage.public_dir', $configs['storage']['public_dir']);
        $container->setParameter('xutim_media.storage.media_path', $configs['storage']['media_path']);
        $container->setParameter('xutim_media.storage.url_prefix', $configs['storage']['url_prefix']);
        $container->setParameter('xutim_media.processing.driver', $configs['processing']['driver']);
        $container->setParameter('xutim_media.processing.optimize', $configs['processing']['optimize']);

        $this->registerStorageAdapter($container, $configs['storage']);
        $this->registerImageProcessor($container, $configs['processing']);
        $this->registerPresetRegistry($container, $configs['presets']);
        $this->registerServices($container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
        $loader->load('repositories.php');
        $loader->load('forms.php');
        $loader->load('actions.php');
        $loader->load('twig.php');
        $loader->load('twig_components.php');
        $loader->load('console.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundleConfigs = $container->getExtensionConfig($this->getAlias());
        /** @var array{models: array<string, array{class: class-string}>} $config */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs,
        );

        $mapping = [];
        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\MediaBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);

        $this->prependAssetMapper($container);
        $this->prependMessengerRouting($container);
    }

    private function prependMessengerRouting(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'routing' => [
                    RegenerateVariantsMessage::class => 'async',
                ],
            ],
        ]);
    }

    private function prependAssetMapper(ContainerBuilder $container): void
    {
        if (!$this->isAssetMapperAvailable($container)) {
            return;
        }

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    __DIR__ . '/../../assets' => '@xutim/media-bundle',
                ],
            ],
        ]);
    }

    private function isAssetMapperAvailable(ContainerBuilder $container): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        /** @var array<string, array{path: string}> $bundlesMetadata */
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');

        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
    }

    /**
     * @param array{public_dir: string, media_path: string, url_prefix: string} $storageConfig
     */
    private function registerStorageAdapter(ContainerBuilder $container, array $storageConfig): void
    {
        $definition = new Definition(LocalStorageAdapter::class, [
            $storageConfig['public_dir'],
            $storageConfig['media_path'],
            $storageConfig['url_prefix'],
        ]);

        $container->setDefinition(LocalStorageAdapter::class, $definition);
        $container->setAlias(StorageAdapterInterface::class, LocalStorageAdapter::class);
    }

    /**
     * @param array{driver: string, optimize: bool} $processingConfig
     */
    private function registerImageProcessor(ContainerBuilder $container, array $processingConfig): void
    {
        $definition = new Definition(SpatieImageProcessor::class, [
            $processingConfig['driver'],
            $processingConfig['optimize'],
        ]);

        $container->setDefinition(SpatieImageProcessor::class, $definition);
        $container->setAlias(ImageProcessorInterface::class, SpatieImageProcessor::class);
    }

    /**
     * @param array<string, array{max_width: int, max_height: int, fit_mode: string, quality: array<string, int>, use_focal_point: bool, formats: list<string>, responsive_widths: list<int>}> $presetsConfig
     */
    private function registerPresetRegistry(ContainerBuilder $container, array $presetsConfig): void
    {
        $definition = new Definition(PresetRegistry::class, [$presetsConfig]);
        $container->setDefinition(PresetRegistry::class, $definition);
    }

    private function registerServices(ContainerBuilder $container): void
    {
        $container->setDefinition(VariantPathResolver::class, new Definition(VariantPathResolver::class, [
            new Reference(StorageAdapterInterface::class),
        ]));

        $container->setDefinition(VariantGenerator::class, new Definition(VariantGenerator::class, [
            new Reference(ImageProcessorInterface::class),
            new Reference(VariantPathResolver::class),
            new Reference(StorageAdapterInterface::class),
            new Reference(PresetRegistry::class),
        ]));

        $container->setDefinition(VariantCleaner::class, new Definition(VariantCleaner::class, [
            new Reference(StorageAdapterInterface::class),
            new Reference(VariantPathResolver::class),
            new Reference(PresetRegistry::class),
        ]));

        $container->setDefinition(BlurHashGenerator::class, new Definition(BlurHashGenerator::class, [
            new Reference(StorageAdapterInterface::class),
        ]));
    }
}
