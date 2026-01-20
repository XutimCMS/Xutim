<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\MessageHandler;

use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;
use Xutim\AnalyticsBundle\Util\IpAddressUtil;
use Xutim\AnalyticsBundle\Util\SessionBucketUtil;

final class CollectAnalyticsMessageHandler
{
    public function __construct(
        private readonly AnalyticsEventFactoryInterface $factory,
        private readonly AnalyticsEventRepositoryInterface $repo,
        private readonly string $appSecret
    ) {
    }

    public function __invoke(CollectAnalyticsMessage $mes): void
    {
        /**
         * list<array{tag: string, id?: string, class?: string, role?: string, text?: string}>
         *
         * @var list<array{tag: string, id?: string, class?: string, role?: string, text?: string}> $clicks
         */
        $clicks = [];
        foreach ($mes->clickedElements as $click) {
            if (!isset($click['tag']) || !is_string($click['tag']) || $click['tag'] === '') {
                continue;
            }

            $row = ['tag' => $click['tag']];

            if (isset($click['id']) && is_string($click['id']) && $click['id'] !== '') {
                $row['id'] = $click['id'];
            }
            if (isset($click['class']) && is_string($click['class']) && $click['class'] !== '') {
                $row['class'] = $click['class'];
            }
            if (isset($click['role']) && is_string($click['role']) && $click['role'] !== '') {
                $row['role'] = $click['role'];
            }
            if (isset($click['text']) && is_string($click['text']) && $click['text'] !== '') {
                $row['text'] = mb_substr($click['text'], 0, 64);
            }

            $clicks[] = $row;
        }

        $userAgent = $mes->userAgent ?? '';
        $isBot = preg_match('/bot|crawler|spider|curl|fetch|preview/i', $userAgent) === 1;

        $anonymizedIp = $mes->clientIp !== null
            ? IpAddressUtil::anonymizeIp($mes->clientIp)
            : '0.0.0.0';

        $sessionBucket = SessionBucketUtil::build(
            $anonymizedIp,
            $userAgent,
            $mes->language,
            30,
            $this->appSecret
        );

        $utmParams = $this->extractUtmParams($mes->queryString);

        $this->repo->save(
            $this->factory->create(
                path: $mes->path,
                referer: $mes->referer,
                userAgent: $mes->userAgent,
                language: $mes->language,
                screenSize: $mes->screenSize,
                loadTimeMs: $mes->loadTimeMs,
                scrollDepth: $mes->scrollDepth,
                clickedElements: $clicks,
                country: $mes->country,
                isBot: $isBot,
                anonymizedIp: $anonymizedIp,
                sessionBucket: $sessionBucket,
                utmSource: $utmParams['utm_source'],
                utmMedium: $utmParams['utm_medium'],
                utmCampaign: $utmParams['utm_campaign']
            ),
            true
        );
    }

    /**
     * @return array{utm_source: ?string, utm_medium: ?string, utm_campaign: ?string}
     */
    private function extractUtmParams(?string $queryString): array
    {
        $result = [
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ];

        if ($queryString === null || $queryString === '') {
            return $result;
        }

        parse_str(ltrim($queryString, '?'), $params);

        if (isset($params['utm_source']) && is_string($params['utm_source']) && $params['utm_source'] !== '') {
            $result['utm_source'] = mb_substr($params['utm_source'], 0, 255);
        }

        if (isset($params['utm_medium']) && is_string($params['utm_medium']) && $params['utm_medium'] !== '') {
            $result['utm_medium'] = mb_substr($params['utm_medium'], 0, 255);
        }

        if (isset($params['utm_campaign']) && is_string($params['utm_campaign']) && $params['utm_campaign'] !== '') {
            $result['utm_campaign'] = mb_substr($params['utm_campaign'], 0, 255);
        }

        return $result;
    }
}
