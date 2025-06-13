<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueFile extends Constraint
{
    public string $message = 'The image already exists.';
}
