<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Settings;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;

class ChangeLanguageContextAction
{
    public function __construct(
        private readonly ContentContext $contentContext,
        private readonly RouterInterface $router,
        private readonly TranslatorAuthChecker $transAuthChecker
    ) {
    }

    public function __invoke(Request $request, string $locale): Response
    {
        $this->transAuthChecker->denyUnlessCanTranslate($locale);
        $this->contentContext->changeLanguage($locale);

        return new RedirectResponse($this->fixReferer($request));
    }

    /**
     * On article edit page, if the url is /article/edit/some-id/en. If the locale is set ("en"),
     * we copy the en version always to the other languages, when we switch context there.
     */
    private function fixReferer(Request $request): string
    {
        $referer = $request->headers->get('referer', '/admin/');
        $adminPos = strpos($referer, '/admin/');
        if ($adminPos === false) {
            return $referer;
        }

        /** @var string $urlWithoutHost */
        $urlWithoutHost = parse_url($referer, PHP_URL_PATH);
        $routeData = $this->router->match($urlWithoutHost);

        if ($routeData['_route'] === 'admin_article_edit') {
            return $this->router->generate('admin_article_edit', ['id' => $routeData['id']]);
        }

        if ($routeData['_route'] === 'admin_') {
            return $this->router->generate('admin_article_edit', ['id' => $routeData['id']]);
        }

        return $referer;
    }
}
