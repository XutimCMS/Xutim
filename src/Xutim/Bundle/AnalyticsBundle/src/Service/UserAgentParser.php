<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Service;

use UAParser\Parser;
use UAParser\Result\Client;

final readonly class UserAgentParser
{
    private const array TABLET_DEVICE_FAMILIES = [
        'iPad',
        'Kindle',
        'Kindle Fire',
        'Nexus 7',
        'Nexus 9',
        'Nexus 10',
        'Galaxy Tab',
        'Surface',
    ];

    private const array MOBILE_DEVICE_FAMILIES = [
        'iPhone',
        'iPod',
        'Galaxy',
        'Pixel',
        'Nexus 4',
        'Nexus 5',
        'Nexus 6',
    ];

    public function __construct(
        private Parser $parser
    ) {
    }

    public function parse(?string $userAgent): Client
    {
        if ($userAgent === null || $userAgent === '') {
            return $this->parser->parse('');
        }

        return $this->parser->parse($userAgent);
    }

    public function parseBrowser(?string $userAgent): string
    {
        $result = $this->parse($userAgent);

        return $result->ua->family ?? 'Unknown';
    }

    public function parseBrowserVersion(?string $userAgent): ?string
    {
        $result = $this->parse($userAgent);

        return $result->ua->toVersion() ?: null;
    }

    public function parseOs(?string $userAgent): string
    {
        $result = $this->parse($userAgent);

        return $result->os->family ?? 'Unknown';
    }

    public function parseOsVersion(?string $userAgent): ?string
    {
        $result = $this->parse($userAgent);

        return $result->os->toVersion() ?: null;
    }

    public function parseDeviceFamily(?string $userAgent): string
    {
        $result = $this->parse($userAgent);

        return $result->device->family ?? 'Unknown';
    }

    /**
     * Categorize device as Mobile, Tablet, or Desktop.
     *
     * ua-parser doesn't provide device type classification, so we use a hybrid approach:
     * 1. Check device family from ua-parser against known mobile/tablet device names
     * 2. Fall back to UA string patterns (Android without 'Mobile' = tablet)
     * 3. Default to Desktop for unrecognized devices
     */
    public function parseDeviceCategory(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'Unknown';
        }

        $result = $this->parse($userAgent);
        $deviceFamily = $result->device->family ?? '';

        foreach (self::TABLET_DEVICE_FAMILIES as $tablet) {
            if (str_contains($deviceFamily, $tablet)) {
                return 'Tablet';
            }
        }

        foreach (self::MOBILE_DEVICE_FAMILIES as $mobile) {
            if (str_contains($deviceFamily, $mobile)) {
                return 'Mobile';
            }
        }

        if (preg_match('/iPad|Tablet/i', $userAgent) === 1) {
            return 'Tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|iPod/i', $userAgent) === 1) {
            if (preg_match('/Android/i', $userAgent) === 1 && preg_match('/Mobile/i', $userAgent) !== 1) {
                return 'Tablet';
            }

            return 'Mobile';
        }

        return 'Desktop';
    }
}
