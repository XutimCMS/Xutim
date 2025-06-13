<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/page-frame/{id<[^/]+>}', name: 'admin_page_frame_show', methods: ['get'])]
class ShowPagePreviewAction extends AbstractController
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader,
        private readonly ContentContext $contentContext,
        private readonly PageRepository $pageRepo
    ) {
    }

    public function __invoke(string $id): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $locale = $this->contentContext->getLanguage();
        $translation = $page->getTranslationByLocaleOrDefault($locale);

        return $this->render($this->themeFinder->getActiveThemePath('/page/base_frame.html.twig'), [
            'page' => $page,
            'color' => $page->getColor()->getValueOrDefaultHex(),
            'translation' => $translation,
            'layout' => $this->layoutLoader->getPageLayoutTemplate($page->getLayout()),
            'locale' => $translation->getLocale(),
            'preTitle' => $translation->getPreTitle(),
            'title' => $translation->getTitle(),
            'subTitle' => $translation->getSubTitle(),
            'featuredImage' => $page->getFeaturedImage(),
            'contentFragments' => $translation->getContent(),
            'isPublished' => $translation->isPublished(),
        ]);
    }
}
