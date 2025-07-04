<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;

class UserLogExtension extends AbstractExtension
{
    public function __construct(
        private readonly LogEventRepository $eventRepository,
        private readonly UserRepositoryInterface $userRepository
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

        $user = $this->userRepository->findOneByEmail($event->getUserIdentifier());
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

        $user = $this->userRepository->findOneByEmail($event->getUserIdentifier());
        if ($user === null) {
            return $event->getUserIdentifier();
        }

        return $user->getName();
    }
}
