# How to Add a Custom User Role in Your Application

This guide explains how to register a custom Symfony role, display it in user forms, and define its permissions and description using the extension points provided by `XutimSecurityBundle`.

---

## 1. Define the Custom Role Constant

In your application, define a role constant. For example:

```php
// src/Entity/Security/User.php

namespace App\Entity\Security;

class User
{
    public const ROLE_EVENT_MANAGER = 'ROLE_EVENT_MANAGER';

    // ...
}
```

---

## 2. Add the Role to the Security Role Hierarchy

Extend the existing role hierarchy using the Symfony compiler pass or kernel-level extension:

```php
// src/Kernel.php (or separate CompilerPass)

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use App\Entity\Security\User;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('security.role_hierarchy.roles')) {
            return;
        }

        $existing = $container->getParameter('security.role_hierarchy.roles');

        $custom = [
            User::ROLE_EVENT_MANAGER => ['ROLE_USER'],
        ];

        foreach ($custom as $key => $value) {
            $existing[$key] = array_unique(array_merge($existing[$key] ?? [], $value));
        }

        $container->setParameter('security.role_hierarchy.roles', $existing);
    }
}
```

This ensures Symfony recognizes your custom role as inheriting permissions from another role (e.g. `ROLE_USER`).

You can also completely change the `config/packages/security.yaml` role_hierarchy section if you need more freedom.

```
    role_hierarchy: !php/const Xutim\SecurityBundle\Security\UserRoles::ROLE_HIERARCHY
```

---

## 3. Register the Role in the Role Provider

Override the default `UserRolesProviderInterface` to include your custom role in the user form.

```php
// src/Service/AppUserRolesProvider.php

namespace App\Service;

use App\Entity\Security\User;
use Xutim\SecurityBundle\Service\UserRolesProviderInterface;
use Xutim\SecurityBundle\Service\UserRolesProvider as XutimUserRolesProvider;

class AppUserRolesProvider implements UserRolesProviderInterface
{
    public function getAvailableRoles(): array
    {
        return [
            ... (new XutimUserRolesProvider())->getAvailableRoles(),
            str_replace('_', ' ', str_replace('ROLE_', '', User::ROLE_EVENT_MANAGER)) => User::ROLE_EVENT_MANAGER,
        ];
    }
}
```

And register it in `services.yaml`:

```yaml
services:
    Xutim\SecurityBundle\Service\UserRolesProviderInterface: '@App\Service\AppUserRolesProvider'
```

---

## 4. Add a Translatable Description

Implement or extend `UserRoleDescriptorProviderInterface` to provide a description for your new role:

```php
// src/Service/AppUserRoleDescriptorProvider.php

namespace App\Service;

use App\Entity\Security\User;
use Symfony\Contracts\Translation\TranslatableMessage;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserRoleDescriptorProviderInterface;

class AppUserRoleDescriptorProvider implements UserRoleDescriptorProviderInterface
{
    public function getRoleDescriptions(): array
    {
        return [
            UserRoles::ROLE_DEVELOPER => new TranslatableMessage('Has full control over the CMS, including the ability to modify the code.'),
            UserRoles::ROLE_ADMIN => new TranslatableMessage('Has full control over the CMS, except for code-related operations.'),
            UserRoles::ROLE_TRANSLATOR => new TranslatableMessage('Can view and translate articles and pages in the assigned languages.'),
            UserRoles::ROLE_EDITOR => new TranslatableMessage('Can create and edit articles, pages, and other types of content.'),
            User::ROLE_EVENT_MANAGER => new TranslatableMessage('Can manage events, schedules, and related content.'),
        ];
    }
}
```

Register it in `services.yaml`:

```yaml
services:
    Xutim\SecurityBundle\Service\UserRoleDescriptorProviderInterface: '@App\Service\AppUserRoleDescriptorProvider'
```

---

## 5. Translate the Role Descriptions (Optional)

Add translations to your `translations/messages.{locale}.yaml`:

```yaml
# translations/messages.en.yaml

Has full control over the CMS, including the ability to modify the code.: 'Has full control over the CMS, including the ability to modify the code.'
Can manage events, schedules, and related content.: 'Can manage events, schedules, and related content.'
```

---

## Note: Override `hasRoleInHierarchy()` if Extending the User Entity

The `XutimUser` class includes a helper method for checking role hierarchy manually:

```php
private function hasRoleInHierarchy(string $role): bool
{
    foreach ($this->getRoles() as $userRole) {
        if ($userRole === $role) {
            return true;
        }

        if (in_array($role, UserRoles::ROLE_HIERARCHY[$userRole], true)) {
            return true;
        }
    }

    return false;
}
```

This only works for statically defined roles in `UserRoles::ROLE_HIERARCHY`.

➡️ If you use dynamic roles injected via the Symfony `security.role_hierarchy.roles` parameter (via compiler pass), **you must override this method** in your `App\Entity\Security\User` class to use the resolved role hierarchy from the container:

```php
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

public function hasRoleInHierarchy(string $role, RoleHierarchyInterface $hierarchy): bool
{
    return in_array($role, $hierarchy->getReachableRoleNames($this->getRoles()), true);
}
```

You can inject `RoleHierarchyInterface` via controller, service, or security voter where needed.

---

## Summary

| Step | Action                                                |
| ---- | ----------------------------------------------------- |
| 1    | Define the role constant                              |
| 2    | Add to Symfony's role hierarchy                       |
| 3    | Register in `UserRolesProviderInterface`              |
| 4    | Describe it via `UserRoleDescriptorProviderInterface` |
| 5    | (Optional) Translate it                               |

This modular design lets you add new roles without modifying the core bundle, keeping your app maintainable and extensible.
