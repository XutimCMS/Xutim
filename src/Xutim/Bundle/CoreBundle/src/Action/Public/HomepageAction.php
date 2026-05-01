<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Twig\ThemeFinder;

class HomepageAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly PageRepository $pageRepository,
        private readonly LayoutLoader $layoutLoader,
        private readonly ThemeFinder $themeFinder,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $homepageId = $this->siteContext->getHomepageId();
        if ($homepageId !== null) {
            $page = $this->pageRepository->find($homepageId);
            if ($page !== null) {
                $translation = $page->getTranslationByLocaleOrDefault($request->getLocale());
                if ($this->isGranted('ROLE_USER') === true || $translation->isPublished() === true) {
                    return $this->render($this->themeFinder->getActiveThemePath('/page/show.html.twig'), [
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
                        'isHomepage' => true,
                    ]);
                }
            }
        }

        return $this->render($this->themeFinder->getActiveThemePath('/homepage/homepage.html.twig'));
    }
}
