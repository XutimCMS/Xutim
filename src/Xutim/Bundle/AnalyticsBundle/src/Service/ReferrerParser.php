<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Service;

final class ReferrerParser
{
    private const array CLICK_ID_SOURCES = [
        'fbclid' => 'Facebook',
        'gclid' => 'Google Ads',
        'gbraid' => 'Google Ads',
        'wbraid' => 'Google Ads',
        'msclkid' => 'Microsoft Ads',
        'twclid' => 'Twitter',
        'ttclid' => 'TikTok',
        'li_fat_id' => 'LinkedIn',
        'igshid' => 'Instagram',
        'mc_eid' => 'Mailchimp',
        'mc_cid' => 'Mailchimp',
        'oly_anon_id' => 'Omeda',
        'oly_enc_id' => 'Omeda',
        'vero_id' => 'Vero',
        '_hsenc' => 'HubSpot',
        'mkt_tok' => 'Marketo',
        'epik' => 'Pinterest',
        'pp' => 'Pocket',
        'ref' => null,
    ];

    private const array UTM_SOURCE_ALIASES = [
        'facebook' => 'Facebook',
        'fb' => 'Facebook',
        'meta' => 'Facebook',
        'instagram' => 'Instagram',
        'ig' => 'Instagram',
        'twitter' => 'Twitter',
        'x' => 'Twitter',
        'x.com' => 'Twitter',
        'linkedin' => 'LinkedIn',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'yt' => 'YouTube',
        'google' => 'Google',
        'bing' => 'Bing',
        'duckduckgo' => 'DuckDuckGo',
        'reddit' => 'Reddit',
        'pinterest' => 'Pinterest',
        'snapchat' => 'Snapchat',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'newsletter' => 'Newsletter',
        'email' => 'Email',
    ];

    private const array SEARCH_ENGINE_DOMAINS = [
        'google' => 'Google',
        'bing' => 'Bing',
        'yahoo' => 'Yahoo',
        'duckduckgo' => 'DuckDuckGo',
        'baidu' => 'Baidu',
        'yandex' => 'Yandex',
        'ecosia' => 'Ecosia',
        'qwant' => 'Qwant',
        'seznam' => 'Seznam',
    ];

    private const array SOCIAL_DOMAINS = [
        'facebook.com' => 'Facebook',
        'fb.com' => 'Facebook',
        'instagram.com' => 'Instagram',
        'twitter.com' => 'Twitter',
        'x.com' => 'Twitter',
        't.co' => 'Twitter',
        'linkedin.com' => 'LinkedIn',
        'lnkd.in' => 'LinkedIn',
        'youtube.com' => 'YouTube',
        'youtu.be' => 'YouTube',
        'tiktok.com' => 'TikTok',
        'reddit.com' => 'Reddit',
        'pinterest.com' => 'Pinterest',
        'pin.it' => 'Pinterest',
        'tumblr.com' => 'Tumblr',
        'snapchat.com' => 'Snapchat',
        'threads.net' => 'Threads',
        'mastodon.social' => 'Mastodon',
    ];

    public function extractDomain(?string $referrer): ?string
    {
        if ($referrer === null || $referrer === '') {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if ($host === false || $host === null) {
            return null;
        }

        return $this->stripWww($host);
    }

    public function detectSourceFromUrl(string $url): ?string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if ($query === null || $query === false) {
            return null;
        }

        parse_str($query, $params);

        foreach (self::CLICK_ID_SOURCES as $param => $source) {
            if (isset($params[$param]) && $params[$param] !== '') {
                if ($source !== null) {
                    return $source;
                }

                return is_string($params[$param]) ? ucfirst($params[$param]) : null;
            }
        }

        $utmSource = $params['utm_source'] ?? null;
        if (is_string($utmSource) && $utmSource !== '') {
            $normalized = strtolower($utmSource);

            return self::UTM_SOURCE_ALIASES[$normalized] ?? ucfirst($utmSource);
        }

        return null;
    }

    public function detectSourceFromReferrer(?string $referrer): ?string
    {
        $domain = $this->extractDomain($referrer);

        if ($domain === null) {
            return null;
        }

        if (isset(self::SOCIAL_DOMAINS[$domain])) {
            return self::SOCIAL_DOMAINS[$domain];
        }

        foreach (self::SEARCH_ENGINE_DOMAINS as $keyword => $name) {
            if (str_contains($domain, $keyword)) {
                return $name;
            }
        }

        return $domain;
    }

    public function isExternal(?string $referrer, string $siteHost): bool
    {
        $referrerDomain = $this->extractDomain($referrer);

        if ($referrerDomain === null) {
            return false;
        }

        $siteDomain = $this->stripWww($siteHost);

        return $referrerDomain !== $siteDomain;
    }

    public function isInternal(?string $referrer, string $siteHost): bool
    {
        $referrerDomain = $this->extractDomain($referrer);

        if ($referrerDomain === null) {
            return false;
        }

        $siteDomain = $this->stripWww($siteHost);

        return $referrerDomain === $siteDomain;
    }

    public function extractPath(?string $referrer): ?string
    {
        if ($referrer === null || $referrer === '') {
            return null;
        }

        $path = parse_url($referrer, PHP_URL_PATH);

        if ($path === false || $path === null) {
            return null;
        }

        return $path;
    }

    private function stripWww(string $host): string
    {
        if (str_starts_with($host, 'www.')) {
            return substr($host, 4);
        }

        return $host;
    }
}
