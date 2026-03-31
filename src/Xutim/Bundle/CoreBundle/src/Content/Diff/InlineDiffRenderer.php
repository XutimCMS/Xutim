<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Diff;

final class InlineDiffRenderer
{
    public function diff(string $old, string $new): string
    {
        if ($old === $new) {
            return $this->escape($new);
        }

        if ($old === '') {
            return '<ins>' . $this->escape($new) . '</ins>';
        }

        if ($new === '') {
            return '<del>' . $this->escape($old) . '</del>';
        }

        $oldTokens = $this->tokenize($old);
        $newTokens = $this->tokenize($new);
        $lcs = $this->buildLcsTable($oldTokens, $newTokens);

        $segments = [];
        $i = 0;
        $j = 0;

        while ($i < count($oldTokens) && $j < count($newTokens)) {
            if ($oldTokens[$i] === $newTokens[$j]) {
                $segments[] = ['op' => 'equal', 'text' => $oldTokens[$i]];
                $i++;
                $j++;
                continue;
            }

            if ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $segments[] = ['op' => 'del', 'text' => $oldTokens[$i]];
                $i++;
                continue;
            }

            $segments[] = ['op' => 'ins', 'text' => $newTokens[$j]];
            $j++;
        }

        while ($i < count($oldTokens)) {
            $segments[] = ['op' => 'del', 'text' => $oldTokens[$i]];
            $i++;
        }

        while ($j < count($newTokens)) {
            $segments[] = ['op' => 'ins', 'text' => $newTokens[$j]];
            $j++;
        }

        return $this->renderSegments($segments);
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $parts = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? $parts : [$text];
    }

    /**
     * @param list<string> $oldTokens
     * @param list<string> $newTokens
     *
     * @return array<int, array<int, int>>
     */
    private function buildLcsTable(array $oldTokens, array $newTokens): array
    {
        $rows = count($oldTokens) + 1;
        $cols = count($newTokens) + 1;
        $table = array_fill(0, $rows, array_fill(0, $cols, 0));

        for ($i = count($oldTokens) - 1; $i >= 0; $i--) {
            for ($j = count($newTokens) - 1; $j >= 0; $j--) {
                if ($oldTokens[$i] === $newTokens[$j]) {
                    $table[$i][$j] = $table[$i + 1][$j + 1] + 1;
                    continue;
                }

                $table[$i][$j] = max($table[$i + 1][$j], $table[$i][$j + 1]);
            }
        }

        return $table;
    }

    private function escape(string $text): string
    {
        return str_replace("\n", '<br>', htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    /**
     * @param list<array{op:string,text:string}> $segments
     */
    private function renderSegments(array $segments): string
    {
        if ($segments === []) {
            return '';
        }

        $merged = [];
        foreach ($segments as $segment) {
            $lastIndex = array_key_last($merged);
            $last = $lastIndex !== null ? $merged[$lastIndex] : null;
            if (is_array($last) && $last['op'] === $segment['op']) {
                $merged[$lastIndex]['text'] .= $segment['text'];
                continue;
            }

            $merged[] = $segment;
        }

        $html = '';
        foreach ($merged as $segment) {
            $escaped = $this->escape($segment['text']);
            $html .= match ($segment['op']) {
                'ins' => '<ins>' . $escaped . '</ins>',
                'del' => '<del>' . $escaped . '</del>',
                default => $escaped,
            };
        }

        return $html;
    }
}
