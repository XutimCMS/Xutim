<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Service;

interface UserRolesProviderInterface
{
    /**
     * @return array<string, string> [label => role]
     */
    public function getAvailableRoles(): array;
}
