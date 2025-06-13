<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Article;

use Doctrine\ORM\EntityManagerInterface;
use Xutim\CoreBundle\Domain\Data\ArticleData;
use Xutim\CoreBundle\Domain\Event\Article\ArticleCreatedEvent;
use Xutim\CoreBundle\Domain\Factory\ArticleFactory;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Message\Command\Article\CreateArticleCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Service\FragmentsFileExtractor;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class CreateArticleHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleRepository $articleRepository,
        private ContentTranslationRepository $contentTransRepo,
        private LogEventRepository $eventRepository,
        private FileRepository $fileRepository,
        private FragmentsFileExtractor $fileExtractor,
        private SearchContentBuilder $searchContentBuilder,
        private ArticleFactory $articleFactory,
        private LogEventFactory $logEventFactory
    ) {
    }

    public function __invoke(CreateArticleCommand $cmd): void
    {
        $file = null;
        if ($cmd->hasFeaturedImage() === true) {
            $file = $this->fileRepository->find($cmd->featuredImageId);
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

        $this->connectFiles($cmd->content, $article);

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
            $article->getFeaturedImage()?->getId()
        );
        $logEntry = $this->logEventFactory->create(
            $article->getId(),
            $cmd->userIdentifier,
            Article::class,
            $event
        );
        $this->eventRepository->save($logEntry, true);
    }

    /**
     * @param EditorBlock $content
     */
    private function connectFiles(array $content, ArticleInterface $article): void
    {
        $files = $this->fileExtractor->extractFiles($content);
        foreach ($files as $filename) {
            /** @var File|null $file */
            $file = $this->fileRepository->findOneBy(['id' => $filename]);
            if ($file === null) {
                continue;
            }

            $file->addArticle($article);
        }
        $this->entityManager->flush();
    }
}
