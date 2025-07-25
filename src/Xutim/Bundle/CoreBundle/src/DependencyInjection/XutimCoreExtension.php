<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Xutim\CoreBundle\Message\Command\Article\PublishScheduledArticlesCommand;
use Xutim\CoreBundle\Message\Command\GenerateSitemapCommand;
use Xutim\SecurityBundle\Message\SendResetPasswordCommand;

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
        $loader->load('scheduler.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundleConfigs = $container->getExtensionConfig($this->getAlias());

        /**
         * @var array{
         *      models: array<string, array{class: class-string}>,
         *      filter_sets?: array<string, mixed>,
         *      message_routing?: array<class-string, string>
         * } $config
         */
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $bundleConfigs
        );

        $this->prependDoctrineResolveTargets($container, $config);
        $this->prependLiipImagineFilterSets($container, $config);
        $this->prependMessengerRouting($container, $config);
    }

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      filter_sets?: array<string, mixed>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependDoctrineResolveTargets(ContainerBuilder $container, array $config): void
    {
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
    }

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      filter_sets?: array<string, mixed>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependLiipImagineFilterSets(ContainerBuilder $container, array $config): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('liip_filters.php');

        /** @var array<string, mixed> $adminFilters */
        $adminFilters = $container->getParameter('xutim_core.admin_filter_sets');

        $userFilters = $config['filter_sets'] ?? [];
        $container->setParameter('xutim_core.filter_sets', $userFilters);

        $mergedFilters = array_merge($adminFilters, $userFilters);

        $container->prependExtensionConfig('liip_imagine', [
            'filter_sets' => $mergedFilters,
        ]);

        $loader->load('twig.php');
    }

    /**
     * @param array{
     *      models: array<string, array{class: class-string}>,
     *      filter_sets?: array<string, mixed>,
     *      message_routing?: array<class-string, string>
     * } $config
     */
    private function prependMessengerRouting(ContainerBuilder $container, array $config): void
    {
        $messagesToRoute = [
            PublishScheduledArticlesCommand::class,
            SendResetPasswordCommand::class,
            GenerateSitemapCommand::class
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
