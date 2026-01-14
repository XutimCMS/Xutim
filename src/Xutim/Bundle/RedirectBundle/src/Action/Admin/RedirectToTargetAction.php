<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

final class RedirectToTargetAction
{
    public function __construct(
        private RedirectRepositoryInterface $repo
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $id = $request->attributes->get('redirect_id');
        $redirect = $this->repo->findById($id);

        if ($redirect === null) {
            throw new NotFoundHttpException();
        }

        $url = $redirect->getTarget();
        $response = new RedirectResponse($url, $redirect->isPermanent() ? 301 : 302);

        if ($this->isExternalUrl($url, $request->getHost())) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private function isExternalUrl(string $url, string $currentHost): bool
    {
        $parsed = parse_url($url);

        if (isset($parsed['host']) === false) {
            return false;
        }

        return $parsed['host'] !== $currentHost
            && str_ends_with($parsed['host'], '.' . $currentHost) === false;
    }
}
