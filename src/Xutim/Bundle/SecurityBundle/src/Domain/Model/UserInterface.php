<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Domain\Model;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Uid\Uuid;

interface UserInterface extends SymfonyUserInterface, PasswordAuthenticatedUserInterface
{
    public function getId(): Uuid;

    public function getName(): string;

    public function getEmail(): string;

    public function getAvatar(): string;

    /**
     * @return list<string>
     */
    public function getTranslationLocales(): array;

    /**
     * @return array<string>
     */
    public function getRoles(): array;

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void;

    public function changePassword(string $password): void;

    /**
     * @param array<string> $roles
     * @param list<string>  $transLocales
     */
    public function changeBasicInfo(string $name, array $roles, array $transLocales): void;

    public function canTranslate(string $locale): bool;

    public function isTranslator(): bool;

    public function isEditor(): bool;

    public function isAdmin(): bool;

    public function isDeveloper(): bool;

    /**
     * @return array{id: Uuid, name: string, email: string, isAdmin: bool, roles: array<string>}
     */
    public function jsonSerialize(): array;
}
