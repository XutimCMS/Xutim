<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Service;

use Xutim\SecurityBundle\Security\UserRoles;

class UserRolesProvider implements UserRolesProviderInterface
{
    public function getAvailableRoles(): array
    {
        return [
            str_replace('ROLE_', '', UserRoles::ROLE_DEVELOPER) => UserRoles::ROLE_DEVELOPER,
            str_replace('ROLE_', '', UserRoles::ROLE_ADMIN) => UserRoles::ROLE_ADMIN,
            str_replace('ROLE_', '', UserRoles::ROLE_TRANSLATOR) => UserRoles::ROLE_TRANSLATOR,
            str_replace('ROLE_', '', UserRoles::ROLE_EDITOR) => UserRoles::ROLE_EDITOR
        ];
    }
}
