<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

class FragmentsFileExtractor
{
    public const string FILE_URL_PREFIX = '/admin/json/file/show/';

    /**
     * @param array{}|array{time: int, blocks: array{}|array{id: string, type: string, data: array<string, mixed>}, version: string} $content
     *
     *
     * @return array<int, string>
     */
    public function extractFiles(array $content): array
    {
        $urls = [];

        // $pattern = '#(?<=\]\()' . self::FILE_URL_PREFIX . '([^)]+)(?="|\))#';
        // preg_match_all($pattern, $markdown, $matches);
        //
        // foreach (array_merge($matches[1]) as $url) {
        //     if (empty($url) === false) {
        //         $urls[] = $url;
        //     }
        // }

        return array_unique($urls);
    }
}
