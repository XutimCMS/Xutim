<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\Article\ArticleCreatedEvent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\File;
use Xutim\CoreBundle\Message\Command\Article\CreateArticleCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\FragmentsFileExtractor;

readonly class CreateArticleHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PageRepository $pageRepository,
        private ArticleRepository $articleRepository,
        private ContentTranslationRepository $contentTransRepo,
        private EventRepository $eventRepository,
        private FileRepository $fileRepository,
        private FragmentsFileExtractor $fileExtractor
    ) {
    }

    public function __invoke(CreateArticleCommand $cmd): void
    {
        $page = $this->pageRepository->find($cmd->pageId);
        if ($page === null) {
            throw new NotFoundHttpException('Page could not be found.');
        }

        $article = new Article(
            $cmd->layout,
            $cmd->preTitle,
            $cmd->title,
            $cmd->subTitle,
            $cmd->slug,
            $cmd->content,
            $cmd->defaultLanguage,
            $cmd->description,
            $page,
            new ArrayCollection()
        );
        $translation = $article->getDefaultTranslation();

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
            $page->getId(),
            $article->getLayout()
        );
        $logEntry = new Event($article->getId(), $cmd->userIdentifier, Article::class, $event);
        $this->eventRepository->save($logEntry, true);
    }

    /**
     * @param array{}|array{time: int, blocks: array{}|array{id: string, type: string, data: array<string, mixed>}, version: string} $content
     */
    private function connectFiles(array $content, Article $article): void
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
