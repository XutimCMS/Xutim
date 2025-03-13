<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Xutim\CoreBundle\Entity\ContentTranslation;

#[\Attribute]
class UniqueSlugLocale extends Constraint
{
    public string $message = 'The combination of slug "{{ slug }}" and locale "{{ locale }}" must be unique.';

    public ?ContentTranslation $existingTranslation = null;

    public function __construct(?ContentTranslation $existingTranslation = null, $options = null)
    {
        parent::__construct($options);
        $this->existingTranslation = $existingTranslation;
    }
}
