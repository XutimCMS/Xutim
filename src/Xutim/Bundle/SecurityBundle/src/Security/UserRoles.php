<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Security;

final class UserRoles
{
    public const string ROLE_USER = 'ROLE_USER';
    public const string ROLE_TRANSLATOR = 'ROLE_TRANSLATOR';
    public const string ROLE_EDITOR = 'ROLE_EDITOR';
    public const string ROLE_ADMIN = 'ROLE_ADMIN';
    public const string ROLE_DEVELOPER = 'ROLE_DEVELOPER';
    public const string ROLE_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';

    public const array ROLE_HIERARCHY = [
        self::ROLE_USER => [],
        self::ROLE_TRANSLATOR => [self::ROLE_USER],
        self::ROLE_EDITOR => [self::ROLE_USER, self::ROLE_TRANSLATOR],
        self::ROLE_ADMIN => [self::ROLE_USER, self::ROLE_TRANSLATOR, self::ROLE_EDITOR, self::ROLE_ALLOWED_TO_SWITCH],
        self::ROLE_DEVELOPER => [self::ROLE_USER, self::ROLE_TRANSLATOR, self::ROLE_EDITOR, self::ROLE_ADMIN, self::ROLE_ALLOWED_TO_SWITCH],
    ];
}
