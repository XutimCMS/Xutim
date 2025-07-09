<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class RefererRouteResolver
{
    public function __construct(
        private readonly UrlMatcherInterface $urlMatcher,
        private readonly RequestContext $context,
    ) {
    }

    /**
     * @return null|array{
     *     _route: string,
     *     _controller?: string,
     *     _locale?: string,
     *     slug?: string
     * }
     */
    public function resolve(Request $request): ?array
    {
        $referer = $request->headers->get('referer');

        if ($referer === null) {
            return null;
        }

        $path = parse_url($referer, PHP_URL_PATH);
        if ($path === null || $path === false) {
            return null;
        }

        $this->context->fromRequest($request);

        try {
            /** @var array{_route: string, _controller?: string, _locale?: string} */
            return $this->urlMatcher->match($path);
        } catch (\Exception) {
            return null;
        }
    }

    public function resolveRouteName(Request $request): ?string
    {
        return $this->resolve($request)['_route'] ?? null;
    }
}
