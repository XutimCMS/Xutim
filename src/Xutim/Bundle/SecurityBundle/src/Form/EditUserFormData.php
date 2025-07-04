<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Form;

final readonly class EditUserFormData
{
    /**
     * @param array<string> $roles
     * @param list<string>  $translationLocales
     */
    public function __construct(
        public string $name,
        public string $email,
        public array $roles,
        public array $translationLocales
    ) {
    }
}
