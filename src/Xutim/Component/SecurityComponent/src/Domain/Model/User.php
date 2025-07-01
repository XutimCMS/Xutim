<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Model;

use Symfony\Component\Uid\Uuid;

class User implements UserInterface
{
    private Uuid $id;

    private string $email;

    private string $name;

    /** @var list<string> */
    private array $roles;

    /**
     * @var string The hashed password
     */
    private string $password;


    private string $avatar;

    /**
     * @var list<string>
     */
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
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function changePassword(string $password): void
    {
        $this->password = $password;
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

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
