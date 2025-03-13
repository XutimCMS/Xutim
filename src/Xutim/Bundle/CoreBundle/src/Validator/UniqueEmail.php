<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Xutim\CoreBundle\Entity\User;

#[\Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'The user with the email "{{ value }}" already exists.';
    public ?User $existingUser = null;
}
