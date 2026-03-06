<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Article;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\Article\ArticleTranslationLocalesUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Message\Command\Article\EditArticleTranslationLocalesCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;

readonly class EditArticleTranslationLocalesHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private LogEventRepository $eventRepository,
        private LogEventFactory $logEventFactory,
    ) {
    }

    public function __invoke(EditArticleTranslationLocalesCommand $cmd): void
    {
        $article = $this->articleRepository->find($cmd->articleId);
        if ($article === null) {
            throw new NotFoundHttpException('Article could not be found.');
        }

        $article->changeAllTranslationLocales($cmd->allTranslationLocales);
        $article->changeTranslationLocales($cmd->translationLocales);
        $this->articleRepository->save($article, true);

        $event = new ArticleTranslationLocalesUpdatedEvent(
            $article->getId(),
            $cmd->translationLocales,
            $article->getUpdatedAt(),
        );

        $logEntry = $this->logEventFactory->create(
            $article->getId(),
            $cmd->userIdentifier,
            Article::class,
            $event,
        );

        $this->eventRepository->save($logEntry, true);
    }
}
