<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Media;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

#[Route('/media', name: 'admin_media_list')]
class ListFilesAction extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
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
    ): Response {
        $searchTerm = $request->query->getString('searchTerm');
        $files = $this->fileRepository->findBySearchTerm($searchTerm);

        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->fileRepository->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/media/list.html.twig', [
            'files' => $pager
        ]);
    }
}
