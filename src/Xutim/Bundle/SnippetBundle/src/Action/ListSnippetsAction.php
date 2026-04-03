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
use Twig\Environment;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
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
        private readonly ContentContext $contentContext,
        private readonly RequestStack $requestStack,
        private readonly AdminUrlGenerator $router
    ) {
    }

    /**
     * @param array<string, list<string>|string> $col
     */
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
                    $this->router->generate('admin_snippet_list', $params)
                );
            }
        }

        $filter = $this->filterBuilder->buildFilter($searchTerm, $page, $pageLength, $orderColumn, $orderDirection, $col);

        $activeLocale = $this->contentContext->getLocale();

        /** @var QueryAdapter<SnippetInterface> $adapter */
        $adapter = new QueryAdapter($this->repo->queryByFilter($filter, $activeLocale));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        $html = $this->twig->render('@XutimSnippet/admin/snippet/snippet_list.html.twig', [
            'snippets' => $pager,
            'filter' => $filter,
            'activeLocale' => $activeLocale,
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
