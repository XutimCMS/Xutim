<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Settings;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\CoreBundle\Routing\RefererRouteResolver;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

class RedirectWithLanguageContextAction extends AbstractController
{
    public function __construct(
        private readonly ContentContext $contentContext,
        private readonly RouterInterface $router,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly RefererRouteResolver $resolver,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly TagTranslationRepository $tagTransRepo
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $locale = $this->contentContext->getLocale();
        $this->transAuthChecker->denyUnlessCanTranslate($locale);
        $url = $this->router->generate('admin_homepage');
        $match = $this->resolver->resolve($request);

        if ($match === null) {
            return new RedirectResponse($url);
        }

        $route = $match['_route'];
        $slug = $match['slug'] ?? null;

        if ($route === 'content_translation_show') {
            $translation = $this->contentTransRepo->findOneBy(['slug' => $slug]);
            if ($translation !== null) {
                $id = $translation->getObject()->getId();
                if ($translation->hasPage()) {
                    $url = $this->router->generate('admin_page_edit', ['id' => $id, '_content_locale' => $locale ]);
                }

                if ($translation->hasArticle()) {
                    $url = $this->router->generate('admin_article_edit', ['id' => $id, '_content_locale' => $locale]);
                }
            }
        }

        if ($route === 'tag_translation_show') {
            $translation = $this->tagTransRepo->findOneBy(['slug' => $slug]);
            if ($translation !== null) {
                $url = $this->router->generate('admin_tag_edit', ['id' => $translation->getTag()->getId(), '_content_locale' => $locale]);
            }
        }

        return new RedirectResponse($url);
    }
}
