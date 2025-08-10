<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Util;

class IpAddressUtil
{
    public static function anonymizeIp(string $ip): string
    {
        $empty = '0.0.0.0';
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $ret = preg_replace('/\.\d+$/', '.0', $ip);
            return $ret === null ? $empty : $ret;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $ret = preg_replace('/:[0-9a-f]+(:[0-9a-f]+){0,4}$/i', '::', $ip);

            return $ret === null ? $empty : $ret;
        }

        return '0.0.0.0';
    }
}
