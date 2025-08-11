<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Action\Public;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;

class CollectAnalyticsAction
{
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $path = isset($data['path']) && is_string($data['path']) ? $data['path'] : null;
        if ($path === null || $path === '') {
            return new JsonResponse(['error' => 'Missing path'], 400);
        }

        /** @var list<array<string, mixed>> $clicks */
        $clicks = isset($data['clicks']) && is_array($data['clicks']) ? $data['clicks'] : [];

        $screenSize = isset($data['screenSize']) && is_string($data['screenSize']) ? $data['screenSize'] : null;
        $loadTimeMs = (isset($data['loadTime']) && (is_int($data['loadTime']) || (is_string($data['loadTime']) && is_numeric($data['loadTime']))))
            ? (int) $data['loadTime']
            : null;
        $scrollDepth = (isset($data['scrollDepth']) && (is_int($data['scrollDepth']) || (is_string($data['scrollDepth']) && is_numeric($data['scrollDepth']))))
            ? (int) $data['scrollDepth']
            : null;

        $referer = $request->headers->get('Referer');
        $userAgent = $request->headers->get('User-Agent');
        $language = $request->headers->get('Accept-Language');
        $proxyIp = $request->headers->get('CF-Connecting-IP');
        $clientIp = $proxyIp ?: $request->getClientIp();
        $country = $request->headers->get('CF-IPCountry');
        $hostname = $request->getHost();

        $referrerDomain = null;
        if ($referer !== null) {
            $host = parse_url($referer, PHP_URL_HOST);
            $referrerDomain = is_string($host) ? $host : null;
        }

        $this->bus->dispatch(new CollectAnalyticsMessage(
            path: $path,
            screenSize: $screenSize,
            loadTimeMs: $loadTimeMs,
            scrollDepth: $scrollDepth,
            clickedElements: $clicks,
            referer: $referer,
            userAgent: $userAgent,
            language: $language,
            clientIp: $clientIp,
            country: $country,
            hostname: $hostname,
            referrerDomain: $referrerDomain,
        ));

        return new JsonResponse(
            ['ok' => true],
            Response::HTTP_OK,
            ['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0', 'Pragma' => 'no-cache']
        );
    }
}
