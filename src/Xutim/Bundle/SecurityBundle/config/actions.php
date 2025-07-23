<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SecurityBundle\Action\Admin\CreateUserAction;
use Xutim\SecurityBundle\Action\Admin\DeleteUserAction;
use Xutim\SecurityBundle\Action\Admin\EditUserAction;
use Xutim\SecurityBundle\Action\Admin\ListUsersAction;
use Xutim\SecurityBundle\Action\Admin\SendResetPasswordAction;
use Xutim\SecurityBundle\Action\Admin\ShowUserAction;
use Xutim\SecurityBundle\Action\Security\CheckEmailAction;
use Xutim\SecurityBundle\Action\Security\ForgotPasswordRequestAction;
use Xutim\SecurityBundle\Action\Security\LoginAction;
use Xutim\SecurityBundle\Action\Security\LogoutAction;
use Xutim\SecurityBundle\Action\Security\ResetPasswordAction;
use Xutim\SecurityBundle\Action\Security\ShowProfileAction;
use Xutim\SecurityBundle\Action\Security\UserChangePasswordAction;
use Xutim\SecurityBundle\Repository\ResetPasswordRequestRepository;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Service\UserStorage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CreateUserAction::class)
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$userStorage', service(UserStorage::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(EditUserAction::class)
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$userStorage', service(UserStorage::class))
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->arg('$resetPasswordRequestRepository', service(ResetPasswordRequestRepository::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(ListUsersAction::class)
        ->arg('$filterBuilder', service(ListFilterBuilder::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(DeleteUserAction::class)
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$csrfTokenChecker', service(CsrfTokenChecker::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(SendResetPasswordAction::class)
        ->arg('$csrfTokenChecker', service(CsrfTokenChecker::class))
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(ShowUserAction::class)
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->arg('$resetPasswordRequestRepository', service(ResetPasswordRequestRepository::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$authChecker', service(AuthorizationCheckerInterface::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(CheckEmailAction::class)
        ->arg('$resetPasswordHelper', service(ResetPasswordHelperInterface::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(ForgotPasswordRequestAction::class)
        ->arg('$mailer', service(MailerInterface::class))
        ->arg('$resetPasswordHelper', service(ResetPasswordHelperInterface::class))
        ->arg('$userRepo', service(UserRepositoryInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(LoginAction::class)
        ->arg('$authenticationUtils', service(AuthenticationUtils::class))
        ->arg('$userStorage', service(UserStorage::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(LogoutAction::class)
        ->tag('controller.service_arguments')
    ;

    $services->set(ResetPasswordAction::class)
        ->arg('$resetPasswordHelper', service(ResetPasswordHelperInterface::class))
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$router', service(UrlGeneratorInterface::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(ShowProfileAction::class)
        ->arg('$userStorage', service(UserStorage::class))
        ->arg('$twig', service(Environment::class))
        ->tag('controller.service_arguments')
    ;

    $services->set(UserChangePasswordAction::class)
        ->arg('$passwordHasher', service(UserPasswordHasherInterface::class))
        ->arg('$commandBus', service(MessageBusInterface::class))
        ->arg('$formFactory', service(FormFactoryInterface::class))
        ->arg('$twig', service(Environment::class))
        ->arg('$flashNotifier', service(FlashNotifier::class))
        ->arg('$userStorage', service(UserStorage::class))
        ->tag('controller.service_arguments')
    ;
};
