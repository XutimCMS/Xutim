<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\SecurityComponent\Domain\Model\UserInterface as ModelUserInterface;

interface UserInterface extends PasswordAuthenticatedUserInterface, SymfonyUserInterface, ModelUserInterface
{
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
