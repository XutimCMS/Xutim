<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Service;

interface UserRoleDescriptorProviderInterface
{
    /**
     * @return array<string, string> [role => label+description]
     */
    public function getRoleDescriptions(): array;
}
