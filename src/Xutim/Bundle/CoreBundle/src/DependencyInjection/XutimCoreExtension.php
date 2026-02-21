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
        /** @var array{models: array<string, array{class: class-string}>} $configs */
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
        $this->prependMediaPresets($container);
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

    private function prependMediaPresets(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('xutim_media', [
            'presets' => [
                'thumb_small' => [
                    'max_width' => 227,
                    'max_height' => 227,
                    'fit_mode' => 'cover',
                    'quality' => ['avif' => 60, 'webp' => 75, 'jpg' => 80],
                    'use_focal_point' => true,
                    'formats' => ['avif', 'webp', 'jpg'],
                    'responsive_widths' => [227],
                ],
                'thumb_large' => [
                    'max_width' => 300,
                    'max_height' => 300,
                    'fit_mode' => 'cover',
                    'quality' => ['avif' => 60, 'webp' => 75, 'jpg' => 80],
                    'use_focal_point' => true,
                    'formats' => ['avif', 'webp', 'jpg'],
                    'responsive_widths' => [300],
                ],
            ],
        ]);
    }
}
