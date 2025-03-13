<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Symfony\Component\String\UnicodeString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TruncateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncateText']),
            new TwigFilter('truncate_center', [$this, 'truncateTextCenter']),
        ];
    }

    public function truncateText(string $text, int $length): string
    {
        return mb_strimwidth($text, 0, $length, '...');
    }

    public function truncateTextCenter(string $text, int $totalLength, int $lengthAtTheEnd = 4): UnicodeString
    {
        $content = new UnicodeString($text);
        if ($totalLength - 3 >= strlen($text)) {
            return $content;
        }

        return $content->truncate($totalLength - 4)->append('...', $content->slice(-1 * $lengthAtTheEnd)->toString());
    }
}
