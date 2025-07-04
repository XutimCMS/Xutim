<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Message;

use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

final readonly class CreateUserCommand
{
    public Uuid $id;

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
        public string $userIdentifier
    ) {
        $this->id = Uuid::v4();
    }
}
