<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentTranslation;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\ContentTranslationFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Message\Command\ContentTranslation\CreateContentTranslationCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class CreateContentTranslationHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private ContentTranslationRepository $contentTransRepo,
        private PageRepository $pageRepo,
        private ArticleRepository $articleRepo,
        private LogEventRepository $eventRepository,
        private SearchContentBuilder $searchContentBuilder,
        private ContentTranslationFactory $contentTranslationFactory
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

        $translation = $this->contentTranslationFactory->create(
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

        $searchContent = $this->searchContentBuilder->build($translation);
        $searchTagContent = $this->searchContentBuilder->buildTagContent($translation);
        $translation->changeSearchContent($searchContent);
        $translation->changeSearchTagContent($searchTagContent);

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

        $log = $this->logEventFactory->create(
            $translation->getId(),
            $cmd->userIdentifier,
            ContentTranslation::class,
            $event
        );

        $this->eventRepository->save($log, true);
    }
}
