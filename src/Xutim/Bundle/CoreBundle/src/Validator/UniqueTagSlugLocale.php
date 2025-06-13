<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;

#[\Attribute]
class UniqueTagSlugLocale extends Constraint
{
    public string $message = 'The combination of slug "{{ slug }}" and locale "{{ locale }}" must be unique.';

    public ?TagTranslationInterface $existingTranslation = null;

    public function __construct(?TagTranslationInterface $existingTranslation = null, $options = null)
    {
        parent::__construct($options);
        $this->existingTranslation = $existingTranslation;
    }
}
