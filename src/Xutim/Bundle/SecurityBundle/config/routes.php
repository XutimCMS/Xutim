<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
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

return function (RoutingConfigurator $routes) {
    $routes->add('admin_user_new', '/admin/user/new')
        ->methods(['get', 'post'])
        ->controller(CreateUserAction::class);

    $routes->add('admin_user_delete', '/admin/user/delete/{id}')
        ->methods(['post'])
        ->controller(DeleteUserAction::class);

    $routes->add('admin_user_edit', '/admin/user/edit/{id}')
        ->methods(['get', 'post'])
        ->controller(EditUserAction::class);

    $routes->add('admin_user_list', '/admin/user')
        ->methods(['get'])
        ->controller(ListUsersAction::class);

    $routes->add('admin_reset_password_send_token', '/admin/reset-password/send-token/{id}')
        ->methods(['post'])
        ->controller(SendResetPasswordAction::class);

    $routes->add('admin_user_show', '/admin/user/{id}')
        ->methods(['get'])
        ->controller(ShowUserAction::class);

    $routes->add('admin_check_email', '/admin/reset-password/check-email')
        ->controller(CheckEmailAction::class);

    $routes->add('admin_forgot_password_request', '/admin/reset-password')
        ->controller(ForgotPasswordRequestAction::class);

    $routes->add('admin_login', '/admin/login')
        ->controller(LoginAction::class);

    $routes->add('admin_logout', '/admin/logout')
        ->controller(LogoutAction::class);

    $routes->add('admin_reset_password', '/admin/reset/{token}')
        ->methods(['post'])
        ->controller([ResetPasswordAction::class, 'reset']);

    $routes->add('admin_user_profile', '/admin/profile')
        ->controller(ShowProfileAction::class);

    $routes->add('admin_user_change_password', '/admin/profile/change-password')
        ->controller(UserChangePasswordAction::class);
};
