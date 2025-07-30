<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Action\CreateSnippetAction;
use Xutim\SnippetBundle\Action\DeleteSnippetAction;
use Xutim\SnippetBundle\Action\EditSnippetAction;
use Xutim\SnippetBundle\Action\JsonListSnippetsAction;
use Xutim\SnippetBundle\Action\ListSnippetsAction;
use Xutim\SnippetBundle\Action\TranslateSnippetAction;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    
    $services->set(CreateSnippetAction::class)
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$transRepo', service(SnippetTranslationRepositoryInterface::class))
        ->arg('$context', service(ContentContext::class))
        ->arg('$snippetsContext', service(SnippetsContext::class))
        ->arg('$blockContext', service(BlockContext::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->arg('$snippetFactory', service(SnippetFactoryInterface::class))
        ->arg('$snippetTransFactory', service(SnippetTranslationFactoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$snippetVersionPath', '%snippet_routes_version_file%')
        ->tag('controller.service_arguments')
    ;

    $services->set(EditSnippetAction::class)
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$transRepo', service(SnippetTranslationRepositoryInterface::class))
        ->arg('$context', service(ContentContext::class))
        ->arg('$snippetsContext', service(SnippetsContext::class))
        ->arg('$blockContext', service(BlockContext::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->arg('$snippetTransFactory', service(SnippetTranslationFactoryInterface::class))
        ->arg('$transAuthChecker', service(TranslatorAuthChecker::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$entityManager', service(EntityManagerInterface::class))
        ->arg('$snippetVersionPath', '%snippet_routes_version_file%')
        ->tag('controller.service_arguments')
    ;

    $services->set(ListSnippetsAction::class)
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$filterBuilder', service(ListFilterBuilder::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(DeleteSnippetAction::class)
        ->arg('$csrfTokenChecker', service(CsrfTokenChecker::class))
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$transRepo', service(SnippetTranslationRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$router', service(AdminUrlGenerator::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(TranslateSnippetAction::class)
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->arg('$transRepo', service(SnippetTranslationRepositoryInterface::class))
        ->arg('$snippetsContext', service(SnippetsContext::class))
        ->arg('$blockContext', service(BlockContext::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->arg('$snippetTransFactory', service(SnippetTranslationFactoryInterface::class))
        ->arg('$transAuthChecker', service(TranslatorAuthChecker::class))
        ->arg('$csrfTokenChecker', service(CsrfTokenChecker::class))
        ->arg('$snippetVersionPath', '%snippet_routes_version_file%')
        ->tag('controller.service_arguments')
    ;

    $services->set(JsonListSnippetsAction::class)
        ->arg('$repo', service(SnippetRepositoryInterface::class))
        ->tag('controller.service_arguments')
    ;
};
