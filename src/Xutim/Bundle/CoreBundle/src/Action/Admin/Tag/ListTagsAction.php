<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;

class ListTagsAction extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepo,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly ContentContext $contentContext,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 10,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc',
    ): Response {
        /** @var array<string,string> $cols */
        $cols = $request->query->all('col');
        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection, $cols);

        /** @var QueryAdapter<TagInterface> $adapter */
        $adapter = new QueryAdapter($this->tagRepo->queryByFilter($filter, $this->contentContext->getLocale()));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render('@XutimCore/admin/tag/tag_list.html.twig', [
            'tags' => $pager,
            'filter' => $filter
        ]);
    }
}
