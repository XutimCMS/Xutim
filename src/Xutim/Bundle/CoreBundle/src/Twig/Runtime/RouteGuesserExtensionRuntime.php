<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;

class RouteGuesserExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $reqStack,
        private readonly ContentTranslationRepository $repo,
        private readonly RouterInterface $router
    ) {
    }

    public function guessRoute(): string
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return '';
        }

        $currentRouteName = $request->attributes->getString('_route');
        $slug = $request->attributes->getString('slug');
        if ($currentRouteName !== '' && $currentRouteName === 'content_translation_show' && $slug !== '') {
            $content = $this->repo->findOneBy(['slug' => $slug]);
            if ($content !== null) {
                if ($content->hasPage()) {
                    return $this->router->generate('admin_page_list', ['id' => $content->getPage()->getId()]);
                }
                if ($content->hasArticle()) {
                    return $this->router->generate('admin_article_show', ['id' => $content->getArticle()->getId()]);
                }
            }
        }

        return $this->router->generate('admin_homepage');
    }

    public function switchLocaleRoute(string $locale): string
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return '';
        }

        $currentRouteName = $request->attributes->getString('_route');
        $slug = $request->attributes->getString('slug');
        if ($currentRouteName !== '' && $currentRouteName === 'content_translation_show' && $slug !== '') {
            $content = $this->repo->findOneBy(['slug' => $slug]);
            if ($content !== null) {
                $translation = $content->getObject()->getTranslationByLocale($locale);
                if ($translation !== null) {
                    return $this->router->generate('content_translation_show', ['_locale' => $locale, 'slug' => $translation->getSlug()]);
                }
            }
        }

        return $this->router->generate('homepage', ['_locale' => $locale]);
    }
}
