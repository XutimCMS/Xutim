<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use Xutim\EventBundle\Domain\Model\EventTranslationInterface;

final readonly class EventTranslationDto
{
    public function __construct(
        public string $title,
        public string $location,
        public string $description,
        public string $locale,
    ) {
    }

    public static function fromEventTranslation(EventTranslationInterface $trans, string $locale): self
    {
        return new self(
            $trans->getTitle(),
            $trans->getLocation(),
            $trans->getDescription(),
            $locale
        );
    }
}
