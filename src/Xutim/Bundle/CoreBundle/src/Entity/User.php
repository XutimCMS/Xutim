<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Repository\UserRepository;

#[Entity(repositoryClass: UserRepository::class)]
#[Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    use TimestampableTrait;

    public const string COMMAND_USER = 'console-command-user';
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

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'string', length: 180, unique: true, nullable: false)]
    private string $email;

    #[Column(type: 'string', length: 180, unique: true, nullable: false)]
    private string $name;

    /** @var list<string> */
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
     * @param list<string> $roles
     * @param list<string> $locales
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
     * @param list<string> $roles
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
     * @param list<string> $roles
     * @param list<string> $transLocales
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
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array(self::ROLE_USER, $this->roles, true)) {
            $roles[] = self::ROLE_USER;
        }

        return $roles;
    }

    private function hasRoleInHierarchy(string $role): bool
    {
        foreach ($this->getRoles() as $userRole) {
            if ($userRole === $role) {
                return true;
            }

            if (in_array($role, self::ROLE_HIERARCHY[$userRole], true)) {
                return true;
            }
        }

        return false;
    }


    public function isTranslator(): bool
    {
        return $this->hasRoleInHierarchy(self::ROLE_TRANSLATOR);
    }

    public function isEditor(): bool
    {
        return $this->hasRoleInHierarchy(self::ROLE_EDITOR);
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleInHierarchy(self::ROLE_ADMIN);
    }

    public function isDeveloper(): bool
    {
        return $this->hasRoleInHierarchy(self::ROLE_DEVELOPER);
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
     * @return array{id: Uuid, name: string, email: string, isAdmin: bool, roles: list<string>}
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
