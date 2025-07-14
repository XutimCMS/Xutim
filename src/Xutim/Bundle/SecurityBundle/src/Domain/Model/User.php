<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\SecurityBundle\Security\UserRoles;

#[MappedSuperclass]
abstract class User implements UserInterface, JsonSerializable
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'string', length: 180, unique: true, nullable: false)]
    private string $email;

    #[Column(type: 'string', length: 180, unique: true, nullable: false)]
    private string $name;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false)]
    private array $roles;

    /**
     * @var string The hashed password
     */
    #[Column(type: 'string', length: 255, nullable: false)]
    private string $password;


    #[Column(type: 'text', nullable: false)]
    private string $avatar;

    /**
     * @var list<string>
     */
    #[Column(type: 'json', nullable: false)]
    private array $translationLocales;

    /**
     * @param array<string> $roles
     * @param list<string>  $locales
     */
    public function __construct(
        Uuid $id,
        string $email,
        string $name,
        string $password,
        array $roles,
        array $locales,
        string $avatar
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->roles = $roles;
        $this->translationLocales = $locales;
        $this->avatar = $avatar;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePassword(string $password): void
    {
        $this->password = $password;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param array<string> $roles
     * @param list<string>  $transLocales
     */
    public function changeBasicInfo(string $name, array $roles, array $transLocales): void
    {
        $this->name = $name;
        $this->roles = $roles;
        $this->translationLocales = $transLocales;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        /** @var non-empty-string */
        $email = $this->email;

        return $email;
    }

    /**
     * @return list<string>
     */
    public function getTranslationLocales(): array
    {
        return $this->translationLocales;
    }

    public function canTranslate(string $locale): bool
    {
        if ($this->isEditor()) {
            return true;
        }
        if ($this->isTranslator() === false) {
            return false;
        }

        
        return in_array($locale, $this->translationLocales, true);
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array(UserRoles::ROLE_USER, $this->roles, true)) {
            $roles[] = UserRoles::ROLE_USER;
        }

        return $roles;
    }

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


    public function isTranslator(): bool
    {
        return $this->hasRoleInHierarchy(UserRoles::ROLE_TRANSLATOR);
    }

    public function isEditor(): bool
    {
        return $this->hasRoleInHierarchy(UserRoles::ROLE_EDITOR);
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleInHierarchy(UserRoles::ROLE_ADMIN);
    }

    public function isDeveloper(): bool
    {
        return $this->hasRoleInHierarchy(UserRoles::ROLE_DEVELOPER);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return array{id: Uuid, name: string, email: string, isAdmin: bool, roles: array<string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'isAdmin' => $this->isAdmin(),
            'roles' => $this->getRoles()
        ];
    }
}
