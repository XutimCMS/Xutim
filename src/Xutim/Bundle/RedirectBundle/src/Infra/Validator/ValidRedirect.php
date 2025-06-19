<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidRedirect extends Constraint
{
    public string $messageSelf = 'Redirect from {{ source }} to itself is not allowed.';
    public string $messageLoop = 'Circular redirect detected between {{ source }} and {{ target }}.';
    public string $messageEmpty = 'Target URL for {{ source }} must not be empty.';
    public string $messageDuplicate = 'A redirect with the same source already exists.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
