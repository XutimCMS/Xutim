<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\MessageHandler;

use Xutim\AnalyticsBundle\Domain\Factory\AnalyticsEventFactoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;
use Xutim\AnalyticsBundle\Message\CollectAnalyticsMessage;
use Xutim\AnalyticsBundle\Util\IpAddressUtil;

final class CollectAnalyticsMessageHandler
{
    public function __construct(
        private readonly AnalyticsEventFactoryInterface $factory,
        private readonly AnalyticsEventRepositoryInterface $repo,
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

        $ua = $mes->userAgent ?? '';
        $isBot = preg_match('/bot|crawler|spider|curl|fetch|preview/i', $ua) === 1;

        $anonymizedIp = $mes->clientIp !== null
            ? IpAddressUtil::anonymizeIp($mes->clientIp)
            : '0.0.0.0';

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
            ),
            true
        );
    }
}
