<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\MediaFolderRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class ListFilesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepo,
        private readonly MediaFolderRepository $mediaFolderRepo,
        private readonly ListFilterBuilder $filterBuilder
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 18,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc',
        ?string $id = null
    ): Response {
        if ($id !== null) {
            $folder = $this->mediaFolderRepo->find($id);
            if ($folder === null) {
                throw $this->createNotFoundException('The media folder does not exist');
            }
        }
        $searchTerm = $request->query->getString('searchTerm');
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<FileInterface> $adapter */
        $adapter = new QueryAdapter($this->fileRepo->queryByFolderAndFilter($filter, $folder ?? null));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        $folders = $this->mediaFolderRepo->findByParentFolder($folder ?? null);

        return $this->render('@XutimCore/admin/media/list.html.twig', [
            'currentFolder' => $folder ?? null,
            'files' => $pager,
            'folders' => $folders
        ]);
    }
}
