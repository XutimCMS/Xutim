<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/page-frame/{id<[^/]+>}', name: 'admin_page_frame_show', methods: ['get'])]
class ShowPagePreviewAction extends AbstractController
{
    public function __construct(
        private readonly ContentFragmentsConverter $converter,
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader,
        private readonly ContentContext $contentContext,
    ) {
    }

    public function __invoke(Page $page): Response
    {
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
            'mainImageBlock' => $translation->getMainImageBlock(),
            'content' => $this->converter->convertToThemeHtml($translation->getContent(), $this->themeFinder->getActiveThemePath()),
            'contentFragments' => $translation->getContent(),
            'isPublishedAndTranslated' => $translation->isPublished(),
        ]);
    }
}
