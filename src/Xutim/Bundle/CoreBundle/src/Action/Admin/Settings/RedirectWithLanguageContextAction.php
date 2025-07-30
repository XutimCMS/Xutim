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
use Xutim\SecurityBundle\Service\UserStorage;

class RedirectWithLanguageContextAction extends AbstractController
{
    public function __construct(
        private readonly ContentContext $contentContext,
        private readonly RouterInterface $router,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly RefererRouteResolver $resolver,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly TagTranslationRepository $tagTransRepo,
        private readonly UserStorage $userStorage
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $locale = $this->contentContext->getLocale();
        $user = $this->userStorage->getUserWithException();
        $canTranslate = $user->canTranslate($locale);
        if ($canTranslate === false) {
            $locale = $user->getTranslationLocales()[array_key_first($user->getTranslationLocales())];
        }
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
                    $routeName = $canTranslate ? 'admin_page_edit' : 'admin_page_list';
                    $url = $this->router->generate($routeName, ['id' => $id, '_content_locale' => $locale ]);
                }

                if ($translation->hasArticle()) {
                    $routeName = $canTranslate ? 'admin_article_edit' : 'admin_article_show';
                    $url = $this->router->generate($routeName, ['id' => $id, '_content_locale' => $locale]);
                }
            }
        }

        if ($route === 'tag_translation_show') {
            $translation = $this->tagTransRepo->findOneBy(['slug' => $slug]);
            if ($translation !== null) {
                $routeName = $canTranslate ? 'admin_tag_edit' : 'admin_tag_show';
                $url = $this->router->generate($routeName, ['id' => $translation->getTag()->getId(), '_content_locale' => $locale]);
            }
        }

        return new RedirectResponse($url);
    }
}
