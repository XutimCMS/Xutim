<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

final class RedirectToContentTranslationAction
{
    public function __construct(
        private RedirectRepositoryInterface $repo,
        private RouterInterface $router
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $id = $request->attributes->get('redirect_id');
        $redirect = $this->repo->findById($id);

        if ($redirect === null) {
            throw new NotFoundHttpException();
        }

        $url = $this->router->generate('content_translation_show', [
            'slug' => $redirect->getTargetContentTranslation()->getSlug(),
            '_locale' => $redirect->getTargetContentTranslation()->getLocale(),
        ]);

        return new RedirectResponse($url, $redirect->isPermanent() ? 301 : 302);
    }
}
