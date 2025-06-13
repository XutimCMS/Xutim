<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\CoreBundle\Twig\ThemeFinder;

class SearchAction extends AbstractController
{
    public function __invoke(
        ThemeFinder $themeFinder,
        ListFilterBuilder $filterBuilder,
        ContentTranslationRepository $contentRepo,
        Request $request,
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 12,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc',
    ): Response {
        $filter = $filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection);

        [$translations, $_, $pages] = $contentRepo->queryByFilter($filter, $request->getLocale());

        return $this->render($themeFinder->getActiveThemePath('/search/search.html.twig'), [
            'contentTranslations' => $translations,
            'pages' => $pages,
            'currentPage' => $filter->page
        ]);
    }
}
