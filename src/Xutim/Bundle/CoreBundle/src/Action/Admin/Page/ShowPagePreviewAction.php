<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Twig\ThemeFinder;

class ShowPagePreviewAction extends AbstractController
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader,
        private readonly ContentContext $contentContext,
        private readonly PageRepository $pageRepo,
        private readonly ContentDraftRepository $draftRepo,
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

        $draft = null;
        if ($translation->isPublished()) {
            $draft = $this->draftRepo->findDraft($translation);
        }

        return $this->render($this->themeFinder->getActiveThemePath('/page/base_frame.html.twig'), [
            'page' => $page,
            'color' => $page->getColor()->getValueOrDefaultHex(),
            'translation' => $translation,
            'layout' => $this->layoutLoader->getPageLayoutTemplate($page->getLayout()),
            'locale' => $translation->getLocale(),
            'preTitle' => $draft?->getPreTitle() ?? $translation->getPreTitle(),
            'title' => $draft?->getTitle() ?? $translation->getTitle(),
            'subTitle' => $draft?->getSubTitle() ?? $translation->getSubTitle(),
            'featuredImage' => $page->getFeaturedImage(),
            'contentFragments' => $draft?->getContent() ?? $translation->getContent(),
            'isPublished' => $translation->isPublished(),
        ]);
    }
}
