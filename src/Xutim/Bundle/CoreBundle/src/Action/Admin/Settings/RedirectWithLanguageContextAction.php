<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Settings;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\CoreBundle\Routing\RefererRouteResolver;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

#[Route('/settings/redirect-with-language-context/{locale}', name: 'admin_settings_redirect_with_language_context', methods: ['get'])]
class RedirectWithLanguageContextAction
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

    public function __invoke(Request $request, string $locale): Response
    {
        $this->transAuthChecker->denyUnlessCanTranslate($locale);
        $url = $this->router->generate('admin_homepage');
        $match = $this->resolver->resolve($request);

        if ($match === null) {
            return new RedirectResponse($url);
        }

        $route = $match['_route'];
        $slug = $match['slug'] ?? null;

        $this->contentContext->changeLanguage($locale);


        if ($route === 'content_translation_show') {
            $translation = $this->contentTransRepo->findOneBy(['slug' => $slug]);
            if ($translation !== null) {
                $id = $translation->getObject()->getId();
                if ($translation->hasPage()) {
                    $url = $this->router->generate('admin_page_edit', ['id' => $id]);
                }

                if ($translation->hasArticle()) {
                    $url = $this->router->generate('admin_article_edit', ['id' => $id]);
                }
            }
        }

        if ($route === 'tag_translation_show') {
            $translation = $this->tagTransRepo->findOneBy(['slug' => $slug]);
            if ($translation !== null) {
                $url = $this->router->generate('admin_tag_edit', ['id' => $translation->getTag()->getId()]);
            }
        }

        return new RedirectResponse($url);
    }
}
