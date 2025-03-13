<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/{_locale}/{slug<[a-zA-Z0-9\-]+>}', priority: -10, name: 'content_translation_show')]
class ShowContentTranslation extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $repository,
        private readonly ContentFragmentsConverter $converter,
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function __invoke(Request $request, string $slug): Response
    {
        $locale = $request->getLocale();
        $translation = $this->repository->findOneBy(['slug' => $slug, 'locale' => $locale]);
        if ($translation === null ||
            ($this->isGranted('ROLE_USER') === false && $translation->isPublished() === false)
        ) {
            throw $this->createNotFoundException(sprintf('The content translation with a slug %s and locale %s was not found.', $slug, $locale));
        }
        $this->repository->incrementVisits($translation);

        if ($translation->hasArticle()) {
            $article = $translation->getArticle();

            return $this->render($this->themeFinder->getActiveThemePath('/article/show.html.twig'), [
                'article' => $article,
                'translation' => $translation,
                'color' => Color::DEFAULT_VALUE_HEX,
                'layout' => $this->layoutLoader->getArticleLayoutTemplate($article->getLayout()),
                'locale' => $translation->getLocale(),
                'preTitle' => $translation->getPreTitle(),
                'title' => $translation->getTitle(),
                'subTitle' => $translation->getSubTitle(),
                'mainImageBlock' => $translation->getMainImageBlock(),
                'content' => $this->converter->convertToThemeHtml($translation->getContent(), $this->themeFinder->getActiveThemePath()),
                'isPublished' => $translation->isPublished()
            ]);
        }

        if ($translation->hasPage()) {
            $page = $translation->getPage();

            return $this->render($this->themeFinder->getActiveThemePath('/page/show.html.twig'), [
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
        
        throw new LogicException('Content translation should have either article or page.');
    }
}
