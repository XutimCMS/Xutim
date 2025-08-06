<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Service;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\SecurityBundle\Security\UserRoles;

class UserRoleDescriptorProvider implements UserRoleDescriptorProviderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getRoleDescriptions(): array
    {
        return [
            UserRoles::ROLE_DEVELOPER => new TranslatableMessage(
                'Has full control over the CMS, including the ability to modify the code.'
            )->trans($this->translator),
            UserRoles::ROLE_ADMIN => new TranslatableMessage(
                'Has full control over the CMS, except for code-related operations.'
            )->trans($this->translator),
            UserRoles::ROLE_TRANSLATOR => new TranslatableMessage(
                'Can view and translate articles and pages in the assigned languages.'
            )->trans($this->translator),
            UserRoles::ROLE_EDITOR => new TranslatableMessage(
                'Can create and edit articles, pages, and other types of content.'
            )->trans($this->translator),
        ];
    }
}
