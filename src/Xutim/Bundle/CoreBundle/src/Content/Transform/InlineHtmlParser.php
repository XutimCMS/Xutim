<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Transform;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Xutim\CoreBundle\Content\Canonical\InlineRun;
use Xutim\CoreBundle\Content\Canonical\TextMark;

final class InlineHtmlParser
{
    public function parse(?string $html): InlineParseResult
    {
        if ($html === null || $html === '') {
            return new InlineParseResult([]);
        }

        try {
            // Wrap the fragment so we can traverse all top-level text nodes and
            // elements in their original order through one parent node.
            $dom = HTMLDocument::createFromString(
                '<!doctype html><div data-inline-root="1">' . $html . '</div>'
            );
        } catch (\Throwable) {
            return new InlineParseResult([new InlineRun(trim($this->normalizeTextSegment($html)))], true);
        }

        $root = $dom->querySelector('div[data-inline-root="1"]');

        if (!$root instanceof Element) {
            return new InlineParseResult([new InlineRun(trim($this->normalizeTextSegment($html)))], true);
        }

        $hadUnsupportedMarkup = false;
        $runs = $this->parseChildren($root, [], $hadUnsupportedMarkup);

        return new InlineParseResult($this->trimBoundaryWhitespace($this->mergeAdjacentRuns($runs)), $hadUnsupportedMarkup);
    }

    /**
     * @param list<TextMark> $marks
     *
     * @return list<InlineRun>
     */
    private function parseChildren(Node $node, array $marks, bool &$hadUnsupportedMarkup): array
    {
        $runs = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof Text) {
                $text = $this->normalizeTextSegment($child->textContent ?? '');
                if ($text !== '') {
                    $runs[] = new InlineRun($text, $marks);
                }
                continue;
            }

            if (!$child instanceof Element) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if ($tag === 'br') {
                $runs[] = new InlineRun("\n", $marks);
                continue;
            }

            $mark = $this->markForElement($child);
            if ($mark !== null) {
                $childMarks = array_merge($marks, [$mark]);
                $runs = array_merge($runs, $this->parseChildren($child, $childMarks, $hadUnsupportedMarkup));
                continue;
            }

            $hadUnsupportedMarkup = true;
            $runs = array_merge($runs, $this->parseChildren($child, $marks, $hadUnsupportedMarkup));
        }

        return $runs;
    }

    private function markForElement(Element $element): ?TextMark
    {
        $href = $element->getAttribute('href');

        return match (strtolower($element->tagName)) {
            'b', 'strong' => new TextMark('bold'),
            'i', 'em' => new TextMark('italic'),
            'u' => new TextMark('underline'),
            's', 'strike', 'del' => new TextMark('strikethrough'),
            'mark' => new TextMark('highlight'),
            'code' => new TextMark('code'),
            'sub' => new TextMark('sub'),
            'sup' => new TextMark('sup'),
            'a' => new TextMark('link', ['href' => $href !== '' ? $href : null]),
            default => null,
        };
    }

    /**
     * @param list<InlineRun> $runs
     *
     * @return list<InlineRun>
     */
    private function mergeAdjacentRuns(array $runs): array
    {
        $merged = [];

        foreach ($runs as $run) {
            if ($merged === []) {
                $merged[] = $run;
                continue;
            }

            $lastIndex = array_key_last($merged);
            $last = $merged[$lastIndex];

            if ($this->marksKey($last) !== $this->marksKey($run)) {
                $merged[] = $run;
                continue;
            }

            $merged[$lastIndex] = new InlineRun($last->text . $run->text, $last->marks);
        }

        return $merged;
    }

    private function marksKey(InlineRun $run): string
    {
        $parts = [];
        foreach ($run->marks as $mark) {
            $parts[] = $mark->type . ':' . json_encode($mark->attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode('|', $parts);
    }

    /**
     * @param list<InlineRun> $runs
     *
     * @return list<InlineRun>
     */
    private function trimBoundaryWhitespace(array $runs): array
    {
        if ($runs === []) {
            return [];
        }

        $firstIndex = array_key_first($runs);
        $trimmed = ltrim($runs[$firstIndex]->text);
        $runs[$firstIndex] = new InlineRun($trimmed, $runs[$firstIndex]->marks);

        $lastIndex = array_key_last($runs);
        $trimmed = rtrim($runs[$lastIndex]->text);
        $runs[$lastIndex] = new InlineRun($trimmed, $runs[$lastIndex]->marks);

        return array_values(array_filter($runs, fn (InlineRun $run): bool => $run->text !== ''));
    }

    private function normalizeTextSegment(string $text): string
    {
        // Editor.js often emits NBSPs (&nbsp;)
        $text = str_replace("\xc2\xa0", ' ', $text);
        $text = preg_replace('/\r\n?/', "\n", $text) ?? $text;
        // Collapse repeated spaces/tabs
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        // Remove padding around explicit line breaks
        $text = preg_replace("/ *\n */u", "\n", $text) ?? $text;

        return $text;
    }
}
