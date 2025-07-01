<?php

declare(strict_types=1);

namespace Xutim\SecurityComponent\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface UserInterface
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
     * @return list<string>
     */
    public function getRoles(): array;

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): void;

    public function changePassword(string $password): void;
}
