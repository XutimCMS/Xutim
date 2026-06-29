<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;
use Xutim\CoreBundle\Twig\ThemeFinder;

class ShowArticlePreviewAction extends AbstractController
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader,
        private readonly ContentContext $contentContext,
        private readonly ArticleRepository $articleRepo,
        private readonly ContentDraftRepository $draftRepo,
        private readonly ReferenceTranslationResolver $referenceTranslationResolver,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $locale = $this->contentContext->getLanguage();
        /** @var ContentTranslationInterface $translation */
        $translation = $this->referenceTranslationResolver->resolveByLocale($article, $locale);

        $draft = null;
        if ($translation->isPublished()) {
            $draft = $this->draftRepo->findDraft($translation);
        }

        return $this->render($this->themeFinder->getActiveThemePath('/article/base_frame.html.twig'), [
            'article' => $article,
            'translation' => $translation,
            'color' => Color::DEFAULT_VALUE_HEX,
            'layout' => $this->layoutLoader->getArticleLayoutTemplate($article->getLayout()),
            'locale' => $translation->getLocale(),
            'preTitle' => $draft?->getPreTitle() ?? $translation->getPreTitle(),
            'title' => $draft?->getTitle() ?? $translation->getTitle(),
            'subTitle' => $draft?->getSubTitle() ?? $translation->getSubTitle(),
            'featuredImage' => $article->getFeaturedImage(),
            'contentFragments' => $draft?->getContent() ?? $translation->getContent(),
            'isPublished' => $translation->isPublished()
        ]);
    }
}
