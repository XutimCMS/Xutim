<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Xutim\SnippetBundle\DataFixtures\LoadRouteSnippetFixture;
use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->set(LoadRouteSnippetFixture::class)
        ->arg('$snippetFactory', service(SnippetFactoryInterface::class))
        ->arg('$translationFactory', service(SnippetTranslationFactoryInterface::class))
        ->tag('doctrine.fixture.orm');
};
