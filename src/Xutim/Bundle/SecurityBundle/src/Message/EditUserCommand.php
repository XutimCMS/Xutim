<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Message;

use Symfony\Component\Uid\Uuid;

final readonly class EditUserCommand
{
    /**
     * @param array<string> $roles
     * @param list<string>  $transLocales
     */
    public function __construct(
        public Uuid $id,
        public string $name,
        public array $roles,
        public array $transLocales,
        public string $userIdentifier
    ) {
    }
}
