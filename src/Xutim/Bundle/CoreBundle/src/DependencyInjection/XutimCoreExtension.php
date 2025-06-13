<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class XutimCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>, filter_sets?: array<string, mixed>} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $container->setParameter('snippet_routes_version_file', '%kernel.cache_dir%/snippet_routes.version');

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_core.model.%s.class', $alias), $modelConfig['class']);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');
        $loader->load('twig_components.php');
        $loader->load('repositories.php');
        $loader->load('factories.php');
        $loader->load('forms.php');
        $loader->load('routing.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundleConfigs = $container->getExtensionConfig($this->getAlias());
        /** @var array{models: array<string, array{class: class-string}>, filter_sets?: array<string, mixed>} $config */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $mapping = [];
        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\CoreBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);


        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('liip_filters.php');

        /** @var array<string, mixed> $adminFilters */
        $adminFilters = $container->getParameter('xutim_core.admin_filter_sets');

        $userFilters = [];
        if (array_key_exists('filter_sets', $config) && $config['filter_sets'] !== []) {
            $userFilters = $config['filter_sets'];
        }
        $container->setParameter('xutim_core.filter_sets', $userFilters);

        $mergedFilters = array_merge($adminFilters, $userFilters);
        $container->prependExtensionConfig('liip_imagine', [
            'filter_sets' => $mergedFilters
        ]);

        $loader->load('twig.php');
    }
}
