<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Xutim\CoreBundle\Context\NewsContext;
use Xutim\CoreBundle\Twig\ThemeFinder;

class ShowNewsAction extends AbstractController
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly NewsContext $newsContext
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter]
        int $page = 1,
    ): Response {
        $pager = $this->newsContext->getNews($request->getLocale(), $page);

        return $this->render($this->themeFinder->getActiveThemePath('/news/news.html.twig'), [
            'articles' => $pager,
            'pages' => $pager->getNbPages(),
            'currentPage' => $page
        ]);
    }
}
