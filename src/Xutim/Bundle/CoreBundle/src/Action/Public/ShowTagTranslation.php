<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Public;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;

use Xutim\CoreBundle\Twig\ThemeFinder;

#[Route('/{_locale}/tag/{slug<[a-zA-Z0-9\-]+>}/{page<\d+>?1}', priority: -10, name: 'tag_translation_show')]
class ShowTagTranslation extends AbstractController
{
    public function __construct(
        private readonly TagTranslationRepository $repo,
        private readonly ArticleRepository $articleRepo,
        private readonly ThemeFinder $themeFinder,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function __invoke(Request $request, string $slug, int $page): Response
    {
        $locale = $request->getLocale();
        $translation = $this->repo->findOneBy(['slug' => $slug, 'locale' => $locale]);
        if ($translation === null ||
            ($this->isGranted('ROLE_USER') === false && $translation->getTag()->isPublished() === false)
        ) {
            throw $this->createNotFoundException(sprintf('The tag translation with a slug %s and locale %s was not found.', $slug, $locale));
        }
        if ($page < 1) {
            throw $this->createNotFoundException(sprintf('Page cannot be lower than 1, %s given.', $page));
        }
        $tag = $translation->getTag();

        $filter = new FilterDto('', $page, 12, 'translation.publishedAt', 'desc');
        /** @var QueryAdapter<Article> $adapter */
        $adapter = new QueryAdapter($this->articleRepo->queryPublishedByTagAndFilter($filter, $tag, $locale));
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $filter->page,
            $filter->pageLength
        );

        return $this->render($this->themeFinder->getActiveThemePath('/tag/show.html.twig'), [
            'tag' => $tag,
            'articles' => $pager,
            'translation' => $translation,
            'tagTranslations' => $tag->getTranslations(),
            'color' => $tag->getColor()->getHex(),
            'layout' => $this->layoutLoader->getTagLayoutTemplate($tag->getLayout()),
            'locale' => $translation->getLocale(),
            'title' => $translation->getName(),
            'featuredImage' => $tag->getFeaturedImage(),
            'isPublished' => $tag->isPublished()
        ]);
    }
}
