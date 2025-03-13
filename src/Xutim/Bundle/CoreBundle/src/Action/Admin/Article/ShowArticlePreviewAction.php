<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/article-frame/{id<[^/]+>}', name: 'admin_article_frame_show', methods: ['get'])]
class ShowArticlePreviewAction extends AbstractController
{
    public function __construct(
        private readonly ContentFragmentsConverter $converter,
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader,
        private readonly ContentContext $contentContext,
    ) {
    }

    public function __invoke(Article $article): Response
    {
        $locale = $this->contentContext->getLanguage();
        $translation = $article->getTranslationByLocaleOrDefault($locale);

        return $this->render($this->themeFinder->getActiveThemePath('/article/base_frame.html.twig'), [
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
}
