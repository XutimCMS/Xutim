<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Service\ListFilterBuilder;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

class ListSnippetsAction
{
    private const string SESSION_KEY = 'snippet_list_filters';

    public function __construct(
        private readonly SnippetRepositoryInterface $repo,
        private readonly ListFilterBuilder $filterBuilder,
        private readonly Environment $twig,
        private readonly SiteContext $siteContext,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(
        #[MapQueryParameter]
        string $searchTerm = '',
        #[MapQueryParameter]
        int $page = 1,
        #[MapQueryParameter]
        int $pageLength = 50,
        #[MapQueryParameter]
        string $orderColumn = '',
        #[MapQueryParameter]
        string $orderDirection = 'asc',
        #[MapQueryParameter(name: 'col', options: ['multiple' => true])]
        array $col = []
    ): Response {
        $sessionFilters = $this->getSessionFilters();

        if (count($col) === 0 && count($sessionFilters) > 0) {
            return new RedirectResponse($this->buildUrlWithFilters($sessionFilters));
        }

        if (count($col) > 0) {
            $this->saveSessionFilters($col);
        }

        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection, $col);

        $visibleLanguages = $filter->colArray('showLanguages');
        if (count($visibleLanguages) === 0) {
            $visibleLanguages = $this->siteContext->getMainLocales();
        }

        /** @var QueryAdapter<SnippetInterface> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        $html = $this->twig->render('@XutimSnippet/admin/snippet/snippet_list.html.twig', [
            'snippets' => $pager,
            'filter' => $filter,
            'visibleLanguages' => $visibleLanguages,
        ]);

        return new Response($html);
    }

    /**
     * @return array<string, string|list<string>>
     */
    private function getSessionFilters(): array
    {
        /** @var array<string, string|list<string>>|null $filters */
        $filters = $this->requestStack->getSession()->get(self::SESSION_KEY);

        return $filters ?? [];
    }

    /**
     * @param array<string, string|list<string>> $filters
     */
    private function saveSessionFilters(array $filters): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $filters);
    }

    /**
     * @param array<string, string|list<string>> $filters
     */
    private function buildUrlWithFilters(array $filters): string
    {
        return $this->urlGenerator->generate('admin_snippet_list', ['col' => $filters]);
    }
}
