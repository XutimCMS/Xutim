<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Uri\Rfc3986\Uri;
use Xutim\CoreBundle\Routing\Dynamic\DynamicMatch;
use Xutim\CoreBundle\Routing\Dynamic\DynamicRouteResolverInterface;
use Xutim\CoreBundle\Routing\Dynamic\ResponseMatch;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

final readonly class RedirectRouteResolver implements DynamicRouteResolverInterface
{
    public function __construct(
        private RedirectRepositoryInterface $redirectRepo,
        private LoggerInterface $logger
    ) {
    }

    public function resolve(string $path, Request $request): ?DynamicMatch
    {
        foreach ($this->redirectRepo->findAll() as $redirect) {
            $expectedPath = $redirect->getSource();
            if ($expectedPath === '') {
                $this->logger->warning('A redirect record has an empty source path.', [
                    'id' => $redirect->getId(),
                    'redirect' => $redirect->getSource(),
                    'target' => $redirect->getTarget(),
                ]);

                continue;
            }

            if ($expectedPath !== $path) {
                continue;
            }

            $response = new RedirectResponse($redirect->getTarget(), $redirect->isPermanent() ? 301 : 302);
            if ($this->isExternalUrl($redirect->getTarget(), $request->getHost())) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            }

            return new ResponseMatch(
                response: $response,
                name: sprintf('xutim_redirect.%s', $redirect->getId())
            );
        }

        return null;
    }

    private function isExternalUrl(string $url, string $currentHost): bool
    {
        $host = Uri::parse($url)?->getHost();
        if ($host === null) {
            return false;
        }

        return $host !== $currentHost
            && str_ends_with($host, '.' . $currentHost) === false;
    }
}
