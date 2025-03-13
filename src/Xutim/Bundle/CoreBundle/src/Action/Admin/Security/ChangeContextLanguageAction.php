<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Security\TranslatorAuthChecker;

#[Route('/settings/change-language-context/{locale}', name: 'admin_settings_change_language_content_context', methods: ['get'])]
class ChangeContextLanguageAction extends AbstractController
{
    public function __invoke(
        Request $request,
        string $locale,
        ContentContext $contentContext,
        RouterInterface $router,
        TranslatorAuthChecker $transAuthChecker
    ): Response {
        $transAuthChecker->denyUnlessCanTranslate($locale);
        $contentContext->changeLanguage($locale);

        return $this->redirect($this->fixReferer($request, $router));
    }

    /**
     * On article edit page, if the url is /article/edit/some-id/en. If the locale is set ("en"),
     * we copy the en version always to the other languages, when we switch context there.
     */
    private function fixReferer(Request $request, RouterInterface $router): string
    {
        $referer = $request->headers->get('referer', '/admin/');
        $adminPos = strpos($referer, '/admin/');
        if ($adminPos === false) {
            return $referer;
        }

        /** @var string $urlWithoutHost */
        $urlWithoutHost = parse_url($referer, PHP_URL_PATH);
        $routeData = $router->match($urlWithoutHost);

        if ($routeData['_route'] === 'admin_article_edit') {
            return $router->generate('admin_article_edit', ['id' => $routeData['id']]);
        }

        if ($routeData['_route'] === 'admin_') {
            return $router->generate('admin_article_edit', ['id' => $routeData['id']]);
        }

        return $referer;
    }
}
