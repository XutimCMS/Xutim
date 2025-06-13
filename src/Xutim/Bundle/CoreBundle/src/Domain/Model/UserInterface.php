<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Uid\Uuid;

interface UserInterface extends PasswordAuthenticatedUserInterface, SymfonyUserInterface
{
    public const COMMAND_USER = 'console-command-user';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_TRANSLATOR = 'ROLE_TRANSLATOR';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_DEVELOPER = 'ROLE_DEVELOPER';
    public const ROLE_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';
    public const ROLE_HIERARCHY = [
        self::ROLE_USER => [],
        self::ROLE_TRANSLATOR => [self::ROLE_USER],
        self::ROLE_EDITOR => [self::ROLE_USER, self::ROLE_TRANSLATOR],
        self::ROLE_ADMIN => [self::ROLE_USER, self::ROLE_TRANSLATOR, self::ROLE_EDITOR, self::ROLE_ALLOWED_TO_SWITCH],
        self::ROLE_DEVELOPER => [self::ROLE_USER, self::ROLE_TRANSLATOR, self::ROLE_EDITOR, self::ROLE_ADMIN, self::ROLE_ALLOWED_TO_SWITCH],
    ];

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void;

    public function changePassword(string $password): void;

    /**
     * @param list<string> $roles
     * @param list<string> $transLocales
     */
    public function changeBasicInfo(string $name, array $roles, array $transLocales): void;

    public function getId(): Uuid;

    public function getName(): string;

    public function getEmail(): string;

    public function getAvatar(): string;

    /**
     * @return list<string>
     */
    public function getTranslationLocales(): array;

    public function canTranslate(string $locale): bool;

    /**
     * @return list<string>
     */
    public function getRoles(): array;

    public function isTranslator(): bool;

    public function isEditor(): bool;

    public function isAdmin(): bool;

    public function isDeveloper(): bool;

    /**
     * @return array{id: Uuid, name: string, email: string, isAdmin: bool, roles: list<string>}
     */
    public function jsonSerialize(): array;
}
