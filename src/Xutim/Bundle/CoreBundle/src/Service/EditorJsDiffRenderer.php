<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Jfcherng\Diff\DiffHelper;

final class EditorJsDiffRenderer
{
    public function diffTitle(?string $old, ?string $new): string
    {
        return $this->inlineDiff($old ?? '', $new ?? '');
    }

    public function diffDescription(?string $old, ?string $new): string
    {
        return $this->inlineDiff($old ?? '', $new ?? '');
    }

    /**
     * Block-aware diff for Editor.js docs.
     *
     * @param EditorBlock $old
     * @param EditorBlock $new
     *
     * @return list<array{
     *   op: 'added'|'removed'|'unchanged'|'modified'|'modified_text',
     *   block?: EditorBlocksUnion,
     *   block_new?: EditorBlocksUnion,
     *   block_old?: EditorBlocksUnion,
     *   html?: string
     * }>
     */
    public function diffContent(array $old, array $new): array
    {
        $oldBlocks = $this->blocksFromDoc($old);
        $newBlocks = $this->blocksFromDoc($new);

        /** @var array<string,int> $oldById */
        $oldById = $this->indexById($oldBlocks);

        $result = [];
        /** @var array<int,true> $visitedOld */
        $visitedOld = [];

        foreach ($newBlocks as $newIndex => $newBlock) {
            $nid = $newBlock['id'];

            $matchedOldIndex = $oldById[$nid] ?? $this->fuzzyMatchIndex($newBlock, $oldBlocks);

            if ($matchedOldIndex !== null) {
                $visitedOld[$matchedOldIndex] = true;
                $oldBlock = $oldBlocks[$matchedOldIndex];

                if ($oldBlock['type'] !== $newBlock['type']) {
                    $result[] = ['op' => 'removed', 'block' => $oldBlock];
                    $result[] = ['op' => 'added',   'block' => $newBlock];
                    continue;
                }

                $result[] = $this->diffBlock($oldBlock, $newBlock);
            } else {
                $result[] = ['op' => 'added', 'block' => $newBlock];
            }
        }

        foreach ($oldBlocks as $oldIndex => $oldBlock) {
            if (!isset($visitedOld[$oldIndex])) {
                $result[] = ['op' => 'removed', 'block' => $oldBlock];
            }
        }

        return $result;
    }

    /**
     * @param EditorBlocksUnion $oldBlock
     * @param EditorBlocksUnion $newBlock
     *
     * @return array{
     *   op: 'unchanged'|'modified'|'modified_text',
     *   block_new: EditorBlocksUnion,
     *   block_old?: EditorBlocksUnion,
     *   html?: string,
     *   meta?: array<string, array{status:'same'|'changed', old?:string, new?:string, html?:string}>
     * }
     */
    private function diffBlock(array $oldBlock, array $newBlock): array
    {
        $typeOld = $oldBlock['type'];
        $typeNew = $newBlock['type'];

        if ($typeOld !== $typeNew) {
            return [
                'op' => 'modified',
                'block_old' => $oldBlock,
                'block_new' => $newBlock,
                'meta' => [
                    '_type' => [
                        'status' => 'changed',
                        'old' => $typeOld,
                        'new' => $typeNew,
                    ],
                ],
            ];
        }

        if (in_array($typeNew, ['paragraph', 'header', 'quote', 'list', 'foldableStart'], true)) {
            $oldTxt = $this->extractText($oldBlock);
            $newTxt = $this->extractText($newBlock);
            if ($oldTxt === $newTxt) {
                return ['op' => 'unchanged', 'block_new' => $newBlock];
            }
            $html = $this->inlineDiff($oldTxt, $newTxt);
            return [
                'op' => 'modified_text',
                'block_new' => $newBlock,
                'block_old' => $oldBlock,
                'html' => $html,
            ];
        }

        if ($typeNew === 'foldableEnd') {
            return ['op' => 'unchanged', 'block_new' => $newBlock];
        }

        $meta = $this->diffStructuredBlock($typeNew, $oldBlock['data'], $newBlock['data']);
        $changed = $this->metaHasChange($meta);

        return [
            'op' => $changed ? 'modified' : 'unchanged',
            'block_new' => $newBlock,
            'block_old' => $oldBlock,
            'meta' => $meta,
        ];
    }

    private function inlineDiff(string $old, string $new): string
    {
        $diffOptions = [
            'context' => 1,
            'ignoreCase' => false,
            'ignoreWhitespace' => false,
        ];

        $rendererOptionsWord = [
            'detailLevel' => 'word',
            'insertMarkers' => ['<ins>', '</ins>'],
            'deleteMarkers' => ['<del>', '</del>'],
            'outputTagAsSpan' => false,
            'showLineNumbers' => false,
            'mergeThreshold' => 0.8,
            'wrapperClasses' => ['diff-wrapper', 'diff-inline'],
        ];

        $html = DiffHelper::calculate($old, $new, 'Inline', $diffOptions, $rendererOptionsWord);
        $onlyLastCol = $this->extractDiffColumnHtml($html);

        if (!$this->hasInsDel($onlyLastCol)) {
            $rendererOptionsChar = $rendererOptionsWord;
            $rendererOptionsChar['detailLevel'] = 'char';
            $diffOptionsChar = $diffOptions;
            $diffOptionsChar['context'] = 0;

            $html2 = \Jfcherng\Diff\DiffHelper::calculate($old, $new, 'Inline', $diffOptionsChar, $rendererOptionsChar);
            $onlyLastCol2 = $this->extractDiffColumnHtml($html2);

            if ($this->hasInsDel($onlyLastCol2)) {
                return $onlyLastCol2;
            }
        }

        return $onlyLastCol;
    }

    /**
     * Convert an EditorBlock doc to a list of blocks.
     *
     * @param EditorBlock $doc
     *
     * @return list<EditorBlocksUnion>
     */
    private function blocksFromDoc(array $doc): array
    {
        if ($doc === []) {
            return [];
        }
        /** @var list<EditorBlocksUnion> $blocks */
        $blocks = $doc['blocks'];
        return $blocks;
    }

    /**
     * @param list<EditorBlocksUnion> $blocks
     *
     * @return array<string,int> id => index
     */
    private function indexById(array $blocks): array
    {
        $out = [];
        foreach ($blocks as $i => $b) {
            $out[$b['id']] = $i;
        }
        return $out;
    }

    /**
     * Try to find the best old index by same type + similar text.
     *
     * @param EditorBlocksUnion       $needle
     * @param list<EditorBlocksUnion> $haystack
     */
    private function fuzzyMatchIndex(array $needle, array $haystack): ?int
    {
        $needleType = $needle['type'];
        $needleText = $this->extractText($needle);

        $bestIndex = null;
        $bestScore = 0.0;

        foreach ($haystack as $i => $cand) {
            if ($cand['type'] !== $needleType) {
                continue;
            }
            $candText = $this->extractText($cand);
            $score = (float) similar_text($candText, $needleText);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
            }
        }

        return $bestScore >= 25.0 ? $bestIndex : null;
    }

    /**
     * Extract normalized text for word-level diffing.
     *
     * @param EditorBlocksUnion $block
     */
    private function extractText(array $block): string
    {
        $type = $block['type'];
        $data = $block['data'];

        if ($type === 'paragraph') {
            /** @var array{text:string} $data */
            return $this->normalizeWs($data['text']);
        }

        if ($type === 'header') {
            /** @var array{text:string, level?:int} $data */
            return $this->normalizeWs($data['text']);
        }

        if ($type === 'quote') {
            /** @var array{text:string, caption:string} $data */
            return $this->normalizeWs($data['text'] . "\n" . $data['caption']);
        }

        if ($type === 'list') {
            /** @var array{style:'checklist'|'ordered'|'unordered', meta:array{start:int,counterType:string}, items:array<int, array{content:string, meta:string, items:array<int,mixed>}>} $data */
            $lines = [];
            foreach ($data['items'] as $item) {
                $lines[] = $this->normalizeWs($item['content']);
            }
            return implode("\n", $lines);
        }

        if ($type === 'mainHeader') {
            /** @var array{pretitle:string, title:string, subtitle:string} $data */
            return $this->normalizeWs($data['pretitle'] . "\n" . $data['title'] . "\n" . $data['subtitle']);
        }

        if ($type === 'foldableStart') {
            /** @var array{title:string, open:bool} $data */
            return $this->normalizeWs($data['title']);
        }

        if ($type === 'foldableEnd') {
            return '';
        }

        return '';
    }

    /**
     * Take the Inline renderer’s <table> output and return the HTML of the last <td> of each data row.
     */
    private function extractDiffColumnHtml(string $tableHtml): string
    {
        if ($tableHtml === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $tableHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//tr[td]/td[last()]');

        $parts = [];
        if ($nodes !== false) {
            foreach ($nodes as $td) {
                /** @var \DOMElement $td */
                $cellHtml = $this->innerHTML($td);

                if ($this->hasInsDel($cellHtml)) {
                    $parts[] = $cellHtml;
                    continue;
                }

                $stripped = preg_replace('/<[^>]+>/u', '', $cellHtml);
                $plain = $stripped !== null ? $stripped : '';
                $plain = trim(html_entity_decode($plain, ENT_QUOTES, 'UTF-8'));
                $plain = preg_replace('/^(?:\xC2\xA0|\\h)+/u', '', $plain);
                $plain = $plain !== null ? $plain : '';

                if ($plain !== '') {
                    $first = $plain[0];
                    if ($first === '+') {
                        $parts[] = '<ins>' . $cellHtml . '</ins>';
                        continue;
                    }
                    if ($first === '-') {
                        $parts[] = '<del>' . $cellHtml . '</del>';
                        continue;
                    }
                }

                $parts[] = $cellHtml;
            }
        }

        $filtered = array_filter($parts, static function ($s): bool {
            return $s !== '';
        });

        return implode('<br/>', $filtered);
    }

    private function innerHTML(\DOMNode $node): string
    {
        $html = '';
        $doc = $node->ownerDocument instanceof \DOMDocument ? $node->ownerDocument : null;

        foreach ($node->childNodes as $child) {
            if ($doc !== null) {
                $piece = $doc->saveHTML($child);
                $html .= is_string($piece) ? $piece : '';
            }
        }

        return $html;
    }

    private function normalizeWs(string $s): string
    {
        // unify NBSP and weird spaces
        $s = str_replace(["\xC2\xA0", "&nbsp;"], ' ', $s);
        // trim each line's indentation
        $tmp = preg_split('/\R/u', $s);
        $lines = is_array($tmp) ? $tmp : [];
        $lines = array_map(static fn ($l) => trim($l), $lines);
        $s = implode("\n", $lines);
        // collapse runs of whitespace to single spaces
        $s = preg_replace('/[ \t]+/u', ' ', $s) ?? $s;
        // collapse multiple blank lines
        $s = preg_replace('/\n{2,}/u', "\n", $s) ?? $s;
        return trim($s);
    }
    private function hasInsDel(string $html): bool
    {
        return stripos($html, '<ins') !== false || stripos($html, '<del') !== false;
    }

    /**
     * @param array<string, array{
     *     status?: 'same'|'changed',
     *     old?:string,
     *     new?:string,
     *     html?:string
     * }> $meta
     */
    private function metaHasChange(array $meta): bool
    {
        foreach ($meta as $info) {
            $status = isset($info['status']) ? $info['status'] : 'same';
            if ($status !== 'same') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string,mixed> $old
     * @param array<string,mixed> $new
     *
     * @return array<string, array{status:'same'|'changed', old?:string, new?:string, html?:string}>
     */
    private function diffStructuredBlock(string $type, array $old, array $new): array
    {
        $scalar = function ($a): string {
            if (is_scalar($a)) {
                return (string)$a;
            }
            if (is_array($a)) {
                $enc = json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return is_string($enc) ? $enc : '';
            }
            return '';
        };
        $field = function (string $name, $o, $n) use ($scalar): array {
            $os = $scalar($o);
            $ns = $scalar($n);

            if (is_string($o) && is_string($n)) {
                $on = $this->normalizeWs($o);
                $nn = $this->normalizeWs($n);
                if ($on === $nn) {
                    return ['status' => 'same', 'old' => $os, 'new' => $ns];
                }
                return [
                    'status' => 'changed',
                    'old' => $os,
                    'new' => $ns,
                    'html' => $this->inlineDiff($o, $n),
                ];
            }

            if ($os === $ns) {
                return ['status' => 'same', 'old' => $os, 'new' => $ns];
            }
            return ['status' => 'changed', 'old' => $os, 'new' => $ns];
        };

        $meta = [];

        switch ($type) {
            case 'xutimImage':
                /** @var array{id:string, url:string, thumbnailUrl?: string} $of */
                $of = $old['file'];
                /** @var array{id:string, url:string, thumbnailUrl?: string} $nf */
                $nf = $new['file'];

                $meta['fileId'] = $field('fileId', $of['id'], $nf['id']);
                $meta['url'] = $field('url', $of['url'], $nf['url']);
                $meta['thumbnailUrl'] = $field('thumbnailUrl', $of['thumbnailUrl'] ?? '', $nf['thumbnailUrl'] ?? '');
                break;

            case 'xutimFile':
                /** @var array{id:string, url:string} $of */
                $of = $old['file'];
                /** @var array{id:string, url:string} $nf */
                $nf = $new['file'];

                $meta['fileId'] = $field('fileId', $of['id'], $nf['id']);
                $meta['url'] = $field('url', $of['url'], $nf['url']);
                break;

            case 'imageRow':
                /** @var array{images: array<int, array{id:string, url:string, thumbnailUrl:string}>} $old */
                /** @var array{images: array<int, array{id:string, url:string, thumbnailUrl:string}>} $new */
                $oi = array_values($old['images']);
                $ni = array_values($new['images']);
                $len = max(count($oi), count($ni));
                for ($i = 0; $i < $len; $i++) {
                    $key = 'image[' . $i . ']';
                    $o = $oi[$i] ?? null;
                    $n = $ni[$i] ?? null;

                    if ($o === null && $n !== null) {
                        $meta[$key] = ['status' => 'changed', 'old' => '', 'new' => $scalar($n)];
                        continue;
                    }
                    if ($o !== null && $n === null) {
                        $meta[$key] = ['status' => 'changed', 'old' => $scalar($o), 'new' => ''];
                        continue;
                    }
                    if ($o !== null && $n !== null) {
                        $same = ($o['id'] === $n['id'])
                            && ($o['url'] === $n['url'])
                            && ($o['thumbnailUrl'] === $n['thumbnailUrl']);
                        $meta[$key] = [
                            'status' => $same ? 'same' : 'changed',
                            'old' => $scalar($o),
                            'new' => $scalar($n),
                        ];
                    }
                }
                break;

            case 'pageLink':
            case 'articleLink':
            case 'xutimTag':
                $meta['id'] = $field('id', $old['id'] ?? '', $new['id'] ?? '');
                break;

            case 'mainHeader':
                $meta['pretitle'] = $field('pretitle', $old['pretitle'] ?? '', $new['pretitle'] ?? '');
                $meta['title'] = $field('title', $old['title'] ?? '', $new['title'] ?? '');
                $meta['subtitle'] = $field('subtitle', $old['subtitle'] ?? '', $new['subtitle'] ?? '');
                break;

            case 'embed':
                $meta['service'] = $field('service', $old['service'] ?? '', $new['service'] ?? '');
                $meta['source'] = $field('source', $old['source'] ?? '', $new['source'] ?? '');
                $meta['caption'] = $field('caption', $old['caption'] ?? '', $new['caption'] ?? '');
                break;

            case 'block':
                $meta['code'] = $field('code', $old['code'] ?? '', $new['code'] ?? '');
                break;

            case 'foldableStart':
                $meta['title'] = $field('title', $old['title'] ?? '', $new['title'] ?? '');
                $meta['open'] = $field('open', $old['open'] ?? false, $new['open'] ?? false);
                break;

            case 'foldableEnd':
                // No data to diff
                break;

            default:
                $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                foreach ($keys as $k) {
                    $meta[$k] = $field($k, $old[$k] ?? '', $new[$k] ?? '');
                }
                break;
        }

        return $meta;
    }
}
