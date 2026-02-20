<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueMedia extends Constraint
{
    public string $message = 'The image already exists.';
}
