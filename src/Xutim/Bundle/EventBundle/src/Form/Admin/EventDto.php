<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\EventBundle\Domain\Model\EventInterface;

final readonly class EventDto
{
    public function __construct(
        public \DateTimeImmutable $startsAt,
        public \DateTimeImmutable $endsAt,
        public string $title,
        public string $location,
        public string $description,
        public string $locale,
        public ?ArticleInterface $article,
        public ?PageInterface $page
    ) {
    }

    public static function fromEvent(EventInterface $event, string $locale): self
    {
        $translation = $event->getTranslationByLocale($locale);

        return new self(
            $event->getStartsAt(),
            $event->getEndsAt(),
            $translation?->getTitle() ?? '',
            $translation?->getLocation() ?? '',
            $translation?->getDescription() ?? '',
            $locale,
            $event->getArticle(),
            $event->getPage()
        );
    }
}
