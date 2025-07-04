<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Form;

use SensitiveParameter;

final readonly class CreateUserFormData
{
    /**
     * @param array<string> $roles
     * @param list<string>  $transLocales
     */
    public function __construct(
        public string $email,
        public string $name,
        #[SensitiveParameter]
        public string $password,
        public array $roles,
        public array $transLocales,
    ) {
    }
}
