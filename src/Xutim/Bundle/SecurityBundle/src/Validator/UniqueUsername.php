<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

#[\Attribute]
class UniqueUsername extends Constraint
{
    public string $message = 'The user with the name "{{ value }}" already exists.';
    public ?UserInterface $existingUser = null;
}
