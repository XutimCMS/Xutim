<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Domain\Factory;

use DateTimeImmutable;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\EventBundle\Domain\Model\EventInterface;
use Xutim\EventBundle\Domain\Model\EventTranslationInterface;

class EventFactory
{
    public function __construct(
        private readonly string $eventClass,
        private readonly string $eventTranslationClass
    ) {
        if (!class_exists($eventClass)) {
            throw new \InvalidArgumentException(sprintf('Event class "%s" does not exist.', $eventClass));
        }

        if (!class_exists($eventTranslationClass)) {
            throw new \InvalidArgumentException(sprintf('EventTranslation class "%s" does not exist.', $eventTranslationClass));
        }
    }

    public function create(
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        string $title,
        string $location,
        string $description,
        string $locale,
        ?ArticleInterface $article,
        ?PageInterface $page,
    ): EventInterface {
        /** @var EventInterface $event */
        $event = new ($this->eventClass)($startsAt, $endsAt, $article, $page);

        /** @var EventTranslationInterface $translation */
        $translation = new ($this->eventTranslationClass)(
            $title,
            $location,
            $description,
            $locale,
            $event
        );
        $event->addTranslation($translation);

        return $event;
    }
}
