<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Util;

class SessionBucketUtil
{
    /**
     * Build a deterministic, privacy-safe session bucket.
     *
     * Always returns a 64-hex SHA-256 HMAC, never null/empty.
     *
     * @param string|null $anonymizedIp  e.g. "192.168.1.0" or "2001:db8:abcd::"
     * @param string|null $userAgent     raw UA
     * @param string|null $language      Accept-Language
     * @param int         $bucketMinutes length of inactivity bucket (e.g. 30)
     * @param string      $appSecret     use %kernel.secret% (or another secret)
     * @param int|null    $now           overrideable timestamp (for tests)
     *
     * @return non-empty-string 64 hex chars
     */
    public static function build(
        ?string $anonymizedIp,
        ?string $userAgent,
        ?string $language,
        int $bucketMinutes,
        string $appSecret,
        ?int $now = null
    ): string {
        $ip = self::norm($anonymizedIp, lower: false);
        $ua = self::norm($userAgent, lower: true);
        $lang = self::norm($language, lower: true);

        $minutes = max(1, $bucketMinutes);
        $bucket = (int) floor(($now ?? time()) / ($minutes * 60));

        // Derive a namespaced salt from the app secret
        $salt = hash_hmac('sha256', 'xutim_analytics_session_v1', $appSecret);

        // Stable, unambiguous material; use HMAC so no secret leaks
        $material = "v=1;ip={$ip};ua={$ua};lang={$lang};bucket={$bucket}";

        return hash_hmac('sha256', $material, $salt);
    }

    /**
     * Normalize possibly-null/empty inputs to a stable form.
     * - trim, collapse whitespace
     * - optional lowercase
     * - use '-' sentinel for empty values (avoids empty strings)
     *
     * @return non-empty-string
     */
    private static function norm(?string $value, bool $lower): string
    {
        $val = trim((string) $value);
        if ($val === '') {
            return '-';
        }

        if ($lower) {
            $val = mb_strtolower($val);
        }

        // Collapse internal whitespace to a single space
        $val = preg_replace('/\s+/', ' ', $val) ?? $val;

        return $val;
    }
}
