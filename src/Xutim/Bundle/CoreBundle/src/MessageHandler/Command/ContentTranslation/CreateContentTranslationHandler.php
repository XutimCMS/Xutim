<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentTranslation;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\PageRepository;

readonly class CreateContentTranslationHandler implements CommandHandlerInterface
{
    public function __construct(
        private ContentTranslationRepository $contentTransRepo,
        private PageRepository $pageRepo,
        private ArticleRepository $articleRepo,
        private EventRepository $eventRepository
    ) {
    }

    public function __invoke(CreateContentTranslationCommand $cmd): void
    {
        $page = $article = null;
        if ($cmd->hasPage()) {
            $page = $this->pageRepo->find($cmd->pageId);
            if ($page === null) {
                throw new NotFoundHttpException(sprintf(
                    'Page "%s" could not be found',
                    $cmd->pageId
                ));
            }
        }
        if ($cmd->hasArticle()) {
            $article = $this->articleRepo->find($cmd->articleId);
            if ($article === null) {
                throw new NotFoundHttpException(sprintf(
                    'Article "%s" could not be found',
                    $cmd->articleId
                ));
            }
        }

        $translation = new ContentTranslation(
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->locale,
            $cmd->description,
            $page,
            $article
        );

        $this->contentTransRepo->save($translation, true);

        $event = new ContentTranslationCreatedEvent(
            $translation->getId(),
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->locale,
            $cmd->description,
            $translation->getCreatedAt(),
            $cmd->pageId,
            $cmd->articleId,
        );

        $log = new Event(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->eventRepository->save($log, true);
    }
}
