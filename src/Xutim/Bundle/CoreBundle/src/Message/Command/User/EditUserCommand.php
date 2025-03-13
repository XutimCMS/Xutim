<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\User;

use Symfony\Component\Uid\Uuid;

final readonly class EditUserCommand
{
    /**
     * @param list<string> $roles
     * @param list<string> $transLocales
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
