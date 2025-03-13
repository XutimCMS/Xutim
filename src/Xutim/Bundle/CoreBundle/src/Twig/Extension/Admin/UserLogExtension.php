<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\UserRepository;

class UserLogExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('article_author_name', [$this, 'getArticleAuthorName']),
            new TwigFilter('article_author_name_last_update', [$this, 'getArticleAuthorNameLastUpdate']),
        ];
    }

    public function getArticleAuthorName(Article $article): string
    {
        $event = $this->eventRepository->findOneBy(['objectId' => $article->getId()], ['recordedAt' => 'ASC']);
        if ($event === null) {
            return 'Unknown';
        }

        $user = $this->userRepository->findOneBy(['email' => $event->getUserIdentifier()]);
        if ($user === null) {
            return $event->getUserIdentifier();
        }

        return $user->getName();
    }

    public function getArticleAuthorNameLastUpdate(Article $article): string
    {
        $event = $this->eventRepository->findOneBy(['objectId' => $article->getId()], ['recordedAt' => 'DESC']);
        if ($event === null) {
            return 'Unknown';
        }

        $user = $this->userRepository->findOneBy(['email' => $event->getUserIdentifier()]);
        if ($user === null) {
            return $event->getUserIdentifier();
        }

        return $user->getName();
    }
}
