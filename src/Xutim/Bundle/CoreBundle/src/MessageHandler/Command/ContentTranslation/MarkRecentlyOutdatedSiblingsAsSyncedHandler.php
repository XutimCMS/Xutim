<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\ContentTranslation;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Message\Command\ContentTranslation\MarkRecentlyOutdatedSiblingsAsSyncedCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\ReferenceSyncService;

readonly class MarkRecentlyOutdatedSiblingsAsSyncedHandler implements CommandHandlerInterface
{
    public function __construct(
        private PageRepository $pageRepo,
        private ArticleRepository $articleRepo,
        private ContentTranslationRepository $contentTransRepo,
        private ReferenceSyncService $referenceSyncService,
    ) {
    }

    public function __invoke(MarkRecentlyOutdatedSiblingsAsSyncedCommand $cmd): int
    {
        $object = null;
        if ($cmd->hasPage()) {
            $object = $this->pageRepo->find($cmd->pageId);
        } elseif ($cmd->hasArticle()) {
            $object = $this->articleRepo->find($cmd->articleId);
        }
        if ($object === null) {
            throw new NotFoundHttpException('Article or page could not be found');
        }

        $count = $this->referenceSyncService->markRecentlyOutdatedSiblingsAsSynced($object);
        $this->contentTransRepo->flush();

        return $count;
    }
}
