<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request,
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
        $shouldClearFilters = isset($col['_clear']);
        unset($col['_clear']);

        if ($shouldClearFilters) {
            $this->clearSessionFilters();
            $col = [];
        } elseif (count($col) > 0) {
            $this->saveSessionFilters($col);
        } elseif (!$request->query->has('col')) {
            $sessionFilters = $this->getSessionFilters();
            if (count($sessionFilters) > 0) {
                $params = $request->query->all();
                $params['col'] = $sessionFilters;

                return new RedirectResponse(
                    $this->urlGenerator->generate('admin_snippet_list', $params)
                );
            }
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

    private function clearSessionFilters(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }
}
