<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
final class XutimSnippetExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        /** @var array{models: array<string, array{class: class-string}>, filter_sets?: array<string, mixed>} $configs */
        $configs = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $container->setParameter('snippet_routes_version_file', '%kernel.cache_dir%/snippet_routes.version');
        /** @var string $versionFile*/
        $versionFile = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/snippet_routes.version');

        if (!file_exists($versionFile)) {
            file_put_contents($versionFile, microtime());
        }

        foreach ($configs['models'] as $alias => $modelConfig) {
            $container->setParameter(sprintf('xutim_snippet.model.%s.class', $alias), $modelConfig['class']);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('repositories.php');
        $loader->load('factories.php');
        $loader->load('contexts.php');
        $loader->load('forms.php');
        $loader->load('routing.php');
        $loader->load('twig.php');
        $loader->load('actions.php');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('fixtures.php');
        }
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
            $interface = sprintf('Xutim\\SnippetBundle\\Domain\\Model\\%sInterface', $camel);
            $mapping[$interface] = $modelConfig['class'];
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => $mapping,
            ],
        ]);
    }
}
