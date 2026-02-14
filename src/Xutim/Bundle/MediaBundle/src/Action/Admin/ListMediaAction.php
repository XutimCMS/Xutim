<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class ListMediaAction
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFolderRepositoryInterface $folderRepository,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request, ?string $id = null): Response
    {
        $folder = null;
        if ($id !== null) {
            $folder = $this->folderRepository->findById(Uuid::fromString($id));
            if ($folder === null) {
                throw new NotFoundHttpException('Folder not found');
            }
        }

        $searchTerm = $request->query->getString('searchTerm', '');
        $page = $request->query->getInt('page', 1);
        $pageLength = $request->query->getInt('pageLength', 18);

        $queryBuilder = $this->mediaRepository->queryByFolderAndSearch($folder, $searchTerm);

        /** @var QueryAdapter<MediaInterface> $adapter */
        $adapter = new QueryAdapter($queryBuilder);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $page, $pageLength);

        $folders = $this->folderRepository->findByParent($folder);

        $content = $this->twig->render('@XutimMedia/admin/list.html.twig', [
            'media' => $pager,
            'folders' => $folders,
            'currentFolder' => $folder,
            'searchTerm' => $searchTerm,
        ]);

        return new Response($content);
    }
}
