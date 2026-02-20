<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Article;

use Xutim\CoreBundle\Domain\Data\ArticleData;
use Xutim\CoreBundle\Domain\Event\Article\ArticleCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\ArticleFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Message\Command\Article\CreateArticleCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

readonly class CreateArticleHandler implements CommandHandlerInterface
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventRepository $eventRepository,
        private MediaRepositoryInterface $mediaRepository,
        private SearchContentBuilder $searchContentBuilder,
        private ArticleFactory $articleFactory,
        private LogEventFactory $logEventFactory
    ) {
    }

    public function __invoke(CreateArticleCommand $cmd): void
    {
        $file = null;
        if ($cmd->hasFeaturedImage() === true) {
            $file = $this->mediaRepository->findById($cmd->featuredImageId);
        }

        $article = $this->articleFactory->create(
            new ArticleData(
                $cmd->layout,
                $cmd->preTitle,
                $cmd->title,
                $cmd->subTitle,
                $cmd->slug,
                $cmd->content,
                $cmd->description,
                $cmd->defaultLanguage,
                $cmd->userIdentifier,
                $file
            )
        );

        $translation = $article->getDefaultTranslation();
        $searchContent = $this->searchContentBuilder->build($translation);
        $translation->changeSearchContent($searchContent);

        $this->articleRepository->save($article);
        $this->contentTransRepo->save($translation, true);

        $event = new ArticleCreatedEvent(
            $article->getId(),
            $translation->getId(),
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->defaultLanguage,
            $cmd->description,
            $article->getCreatedAt(),
            $article->getLayout(),
            $article->getFeaturedImage()?->id()
        );
        $logEntry = $this->logEventFactory->create(
            $article->getId(),
            $cmd->userIdentifier,
            Article::class,
            $event
        );
        $this->eventRepository->save($logEntry, true);
    }
}
