<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class XutimRedirectExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $container->setParameter('redirect_routes_version_file', '%kernel.cache_dir%/redirect_routes.version');
        /** @var string $versionFile*/
        $versionFile = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/redirect_routes.version');

        if (!file_exists($versionFile)) {
            file_put_contents($versionFile, microtime());
        }

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_redirect.model.%s.class', $alias), $modelConfig['class']);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('resolvers.php');
        $loader->load('repositories.php');
        $loader->load('factories.php');
        $loader->load('forms.php');
        $loader->load('actions.php');
        $loader->load('routing.php');
        $loader->load('validators.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Xutim\RedirectBundle\Migrations' => __DIR__ . '/../Migrations',
            ]
        ]);

        $bundleConfigs = $container->getExtensionConfig($this->getAlias());
        /** @var array{models: array<string, array{class: class-string}>} $config */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $mapping = [];
        foreach ($config['models'] as $alias => $modelConfig) {
            $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
            $interface = sprintf('Xutim\\RedirectBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);
    }
}
