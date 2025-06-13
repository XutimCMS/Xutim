<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Domain\Factory;

use Xutim\EventBundle\Domain\Model\EventInterface;
use Xutim\EventBundle\Domain\Model\EventTranslationInterface;

class EventTranslationFactory
{
    public function __construct(
        private readonly string $eventTranslationClass
    ) {
        if (!class_exists($eventTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('EventTranslation class "%s" does not exist.', $eventTranslationClass));
        }
    }

    public function create(
        string $title,
        string $location,
        string $description,
        string $locale,
        EventInterface $event
    ): EventTranslationInterface {
        /** @var EventTranslationInterface $translation */
        $translation = new ($this->eventTranslationClass)(
            $title,
            $location,
            $description,
            $locale,
            $event
        );
        $event->addTranslation($translation);

        return $translation;
    }
}
