<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Infra\Util;

class RedirectNormalizer
{
    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        /** @var string $path */
        $path = preg_replace('#/+#', '/', $path);
        $normalized = rtrim($path, '/');

        return $normalized === '' ? '/' : $normalized;
    }

    public static function normalizeLocale(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        return strtolower($locale);
    }
}
