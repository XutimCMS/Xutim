<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dto\Admin\User;

final readonly class EditUserDto
{
    /**
     * @param list<string> $roles
     * @param list<string> $translationLocales
     */
    public function __construct(
        public string $name,
        public string $email,
        public array $roles,
        public array $translationLocales
    ) {
    }
}
