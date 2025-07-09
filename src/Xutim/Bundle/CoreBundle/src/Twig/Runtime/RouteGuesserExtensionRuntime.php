<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\CoreBundle\Routing\RouteSnippetRegistry;

class RouteGuesserExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $reqStack,
        private readonly ContentTranslationRepository $repo,
        private readonly TagTranslationRepository $tagRepo,
        private readonly RouterInterface $router
    ) {
    }

    public function switchLocaleRoute(string $locale): string
    {
        $request = $this->reqStack->getMainRequest();
        if ($request === null) {
            return '';
        }

        $currentRouteName = $request->attributes->getString('_route');
        $slug = $request->attributes->getString('slug');
        $currentLocale = $request->getLocale();
        switch ($currentRouteName) {
            case 'content_translation_show':
                if ($slug !== '') {
                    $content = $this->repo->findOneBy(['slug' => $slug]);
                    if ($content !== null) {
                        $translation = $content->getObject()->getTranslationByLocale($locale);
                        if ($translation !== null) {
                            return $this->router->generate('content_translation_show', ['_locale' => $locale, 'slug' => $translation->getSlug()]);
                        }
                    }
                }
                break;
            case 'tag_translation_show':
                if ($slug !== '') {
                    $content = $this->tagRepo->findOneBy(['slug' => $slug]);
                    if ($content !== null) {
                        $translation = $content->getTag()->getTranslationByLocale($locale);
                        if ($translation !== null && $content->getTag()->isPublished()) {
                            return $this->router->generate('tag_translation_show', ['_locale' => $locale, 'slug' => $translation->getSlug()]);
                        }
                    }
                }
                break;
        }
        foreach (RouteSnippetRegistry::all() as $route) {
            if ($currentRouteName === sprintf('xutim_%s.%s', $route->routeName, $currentLocale)) {
                $redirectRouteName = sprintf('xutim_%s.%s', $route->routeName, $locale);

                return $this->router->generate($redirectRouteName);
            }
        }

        return $this->router->generate('homepage', ['_locale' => $locale]);
    }
}
