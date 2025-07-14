<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Domain\Factory\UserFactoryInterface;
use Xutim\SecurityBundle\Message\ChangePasswordCommand;
use Xutim\SecurityBundle\Message\CreateUserCommand;
use Xutim\SecurityBundle\Message\DeleteUserCommand;
use Xutim\SecurityBundle\Message\EditUserCommand;
use Xutim\SecurityBundle\Message\SendResetPasswordCommand;
use Xutim\SecurityBundle\MessageHandler\ChangePasswordHandler;
use Xutim\SecurityBundle\MessageHandler\CreateUserHandler;
use Xutim\SecurityBundle\MessageHandler\EditUserHandler;
use Xutim\SecurityBundle\MessageHandler\SendResetPasswordHandler;
use Xutim\SecurityBundle\MessageHandler\UserDeleteHandler;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ChangePasswordHandler::class)
        ->arg('$logEventFactory', service(LogEventFactory::class))
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->tag('messenger.message_handler', [
            'handles' => ChangePasswordCommand::class,
            'bus' => 'command.bus'
        ])
    ;

    $services->set(CreateUserHandler::class)
        ->arg('$logEventFactory', service(LogEventFactory::class))
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->arg('$passwordHasherFactory', service(PasswordHasherFactoryInterface::class))
        ->arg('$userFactory', service(UserFactoryInterface::class))
        ->tag('messenger.message_handler', [
            'handles' => CreateUserCommand::class,
            'bus' => 'command.bus'
        ])
    ;

    $services->set(EditUserHandler::class)
        ->arg('$logEventFactory', service(LogEventFactory::class))
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->tag('messenger.message_handler', [
            'handles' => EditUserCommand::class,
            'bus' => 'command.bus'
        ])
    ;

    $services->set(SendResetPasswordHandler::class)
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->arg('$resetPasswordHelper', service(ResetPasswordHelperInterface::class))
        ->arg('$mailer', service(MailerInterface::class))
        ->arg('$logger', service(LoggerInterface::class))
        ->arg('$urlGenerator', service(UrlGeneratorInterface::class))
        ->arg('$siteContext', service(SiteContext::class))
        ->tag('messenger.message_handler', [
            'handles' => SendResetPasswordCommand::class,
            'bus' => 'command.bus'
        ])
    ;

    $services->set(UserDeleteHandler::class)
        ->arg('$logEventFactory', service(LogEventFactory::class))
        ->arg('$userRepository', service(UserRepositoryInterface::class))
        ->arg('$eventRepository', service(LogEventRepository::class))
        ->tag('messenger.message_handler', [
            'handles' => DeleteUserCommand::class,
            'bus' => 'command.bus'
        ])
    ;
};
