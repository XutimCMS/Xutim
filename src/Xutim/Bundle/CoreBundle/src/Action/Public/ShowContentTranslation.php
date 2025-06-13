<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Twig\ThemeFinder;

class ShowContentTranslation extends AbstractController
{
    public function __construct(
        private readonly ContentTranslationRepository $repository,
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function __invoke(string $slug, string $contentLocale): Response
    {
        $translation = $this->repository->findOneBy(['slug' => $slug, 'locale' => $contentLocale]);

        if ($translation === null ||
            ($this->isGranted('ROLE_USER') === false && $translation->isPublished() === false)
        ) {
            throw $this->createNotFoundException(sprintf('The content translation with a slug %s and locale %s was not found.', $slug, $contentLocale));
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
                'featuredImage' => $article->getFeaturedImage(),
                'contentFragments' => $translation->getContent(),
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
                'featuredImage' => $page->getFeaturedImage(),
                'contentFragments' => $translation->getContent(),
                'isPublished' => $translation->isPublished(),
            ]);
        }
        
        throw new LogicException('Content translation should have either article or page.');
    }
}
