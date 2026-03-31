<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Diff;

use Xutim\CoreBundle\Content\Canonical\CanonicalBlock;
use Xutim\CoreBundle\Content\Canonical\CanonicalDocument;
use Xutim\CoreBundle\Content\Canonical\GalleryImage;
use Xutim\CoreBundle\Content\Canonical\InlineRun;
use Xutim\CoreBundle\Content\Canonical\ListItem;
use Xutim\CoreBundle\Content\Canonical\TextMark;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;

final class CanonicalContentDiffRenderer
{
    public function __construct(
        private readonly CanonicalContentExtractor $extractor,
        private readonly InlineDiffRenderer $inlineDiffRenderer,
    ) {
    }

    public function diffText(string $old, string $new): string
    {
        return $this->inlineDiffRenderer->diff($old, $new);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function diffDocuments(CanonicalDocument $old, CanonicalDocument $new): array
    {
        return $this->diffBlockList($old->blocks, $new->blocks);
    }

    /**
     * @param list<CanonicalBlock> $oldBlocks
     * @param list<CanonicalBlock> $newBlocks
     *
     * @return list<array<string, mixed>>
     */
    private function diffBlockList(array $oldBlocks, array $newBlocks): array
    {
        $pairs = $this->alignBlocks($oldBlocks, $newBlocks);
        $rows = [];

        foreach ($pairs as [$oldBlock, $newBlock]) {
            if ($oldBlock instanceof CanonicalBlock && $newBlock instanceof CanonicalBlock) {
                if ($oldBlock->kind === 'foldable' && $newBlock->kind === 'foldable') {
                    $rows = array_merge($rows, $this->diffFoldable($oldBlock, $newBlock));
                    continue;
                }

                $rows[] = $this->diffSingleBlock($oldBlock, $newBlock);
                continue;
            }

            if ($oldBlock instanceof CanonicalBlock) {
                $rows = array_merge($rows, $this->renderAddedOrRemoved($oldBlock, 'removed'));
                continue;
            }

            if ($newBlock instanceof CanonicalBlock) {
                $rows = array_merge($rows, $this->renderAddedOrRemoved($newBlock, 'added'));
            }
        }

        return $rows;
    }

    /**
     * @param list<CanonicalBlock> $oldBlocks
     * @param list<CanonicalBlock> $newBlocks
     *
     * @return list<array{0:?CanonicalBlock,1:?CanonicalBlock}>
     */
    private function alignBlocks(array $oldBlocks, array $newBlocks): array
    {
        $oldCount = count($oldBlocks);
        $newCount = count($newBlocks);
        $gapPenalty = -0.7;

        $path = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, ''));
        $previousRow = array_fill(0, $newCount + 1, 0.0);
        $currentRow = array_fill(0, $newCount + 1, 0.0);

        for ($j = 1; $j <= $newCount; $j++) {
            $previousRow[$j] = $j * $gapPenalty;
            $path[0][$j] = 'left';
        }

        for ($i = 1; $i <= $oldCount; $i++) {
            $currentRow[0] = $i * $gapPenalty;
            $path[$i][0] = 'up';

            for ($j = 1; $j <= $newCount; $j++) {
                $pairScore = $this->blockPairScore($oldBlocks[$i - 1], $newBlocks[$j - 1]);
                $diag = $pairScore > -1000 ? $previousRow[$j - 1] + $pairScore : -INF;
                $up = $previousRow[$j] + $gapPenalty;
                $left = $currentRow[$j - 1] + $gapPenalty;

                if ($diag >= $up && $diag >= $left) {
                    $currentRow[$j] = $diag;
                    $path[$i][$j] = 'diag';
                    continue;
                }

                if ($up >= $left) {
                    $currentRow[$j] = $up;
                    $path[$i][$j] = 'up';
                    continue;
                }

                $currentRow[$j] = $left;
                $path[$i][$j] = 'left';
            }

            $previousRow = $currentRow;
        }

        $pairs = [];
        $i = $oldCount;
        $j = $newCount;
        while ($i > 0 || $j > 0) {
            $direction = $path[$i][$j] ?? '';
            if ($direction === 'diag') {
                $pairs[] = [$oldBlocks[$i - 1], $newBlocks[$j - 1]];
                $i--;
                $j--;
                continue;
            }

            if ($direction === 'up') {
                $pairs[] = [$oldBlocks[$i - 1], null];
                $i--;
                continue;
            }

            $pairs[] = [null, $newBlocks[$j - 1]];
            $j--;
        }

        return array_reverse($pairs);
    }

    private function blockPairScore(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): float
    {
        if ($oldBlock->kind !== $newBlock->kind) {
            return -10000.0;
        }

        $score = 0.0;
        $sameSourceKey = $oldBlock->sourceKey !== null && $oldBlock->sourceKey === $newBlock->sourceKey;
        if ($sameSourceKey) {
            $score += 2.0;
        }

        $oldText = $this->blockText($oldBlock);
        $newText = $this->blockText($newBlock);

        if ($oldText === $newText) {
            $score += 1.5;
        } elseif ($oldText !== '' || $newText !== '') {
            $similarity = $this->textSimilarity($oldText, $newText);
            if ($similarity < 0.45 && $sameSourceKey === false) {
                return -10000.0;
            }
            $score += max($similarity, $sameSourceKey ? 0.2 : 0.0);
        } else {
            $score += 0.7;
        }

        if ($this->attrsSignature($oldBlock) === $this->attrsSignature($newBlock)) {
            $score += 0.4;
        }

        return $score;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function diffFoldable(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): array
    {
        $rows = [[
            'op' => $this->partsChanged($oldBlock->parts['title'] ?? [], $newBlock->parts['title'] ?? [])
                ? 'modified_text'
                : ($this->attrsChanged($oldBlock->attrs, $newBlock->attrs) ? 'modified' : 'unchanged'),
            'kind' => 'foldable_start',
            'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
            'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
            'attrs' => $newBlock->attrs,
            'body_html' => $this->inlineHtmlDiff($oldBlock->parts['title'] ?? [], $newBlock->parts['title'] ?? []),
            'props' => $this->diffAttrs($oldBlock->attrs, $newBlock->attrs, ['anchor', 'align']),
        ]];

        $rows = array_merge($rows, $this->diffBlockList($oldBlock->children, $newBlock->children));

        $rows[] = [
            'op' => 'unchanged',
            'kind' => 'foldable_end',
            'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
            'render_key' => ($newBlock->sourceKey ?? $oldBlock->sourceKey) !== null ? ($newBlock->sourceKey ?? $oldBlock->sourceKey) . '_end' : null,
            'attrs' => [],
        ];

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function diffSingleBlock(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): array
    {
        $props = $this->diffAttrs($oldBlock->attrs, $newBlock->attrs);

        return match ($newBlock->kind) {
            'paragraph', 'heading' => [
                'op' => $this->partsChanged($oldBlock->body, $newBlock->body) ? 'modified_text' : ($props === [] ? 'unchanged' : 'modified'),
                'kind' => $newBlock->kind,
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'body_html' => $this->inlineHtmlDiff($oldBlock->body, $newBlock->body),
                'props' => $props,
            ],
            'list' => [
                'op' => $this->listChanged($oldBlock, $newBlock) ? 'modified_text' : ($props === [] ? 'unchanged' : 'modified'),
                'kind' => 'list',
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'body_html' => $this->inlineDiffRenderer->diff($this->blockText($oldBlock), $this->blockText($newBlock)),
                'items' => $this->renderListItems($newBlock->listItems),
                'props' => $props,
            ],
            'quote' => [
                'op' => $this->quoteChanged($oldBlock, $newBlock) ? 'modified' : ($props === [] ? 'unchanged' : 'modified'),
                'kind' => 'quote',
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'parts' => [
                    'text' => [
                        'html' => $this->runsToHtml($newBlock->parts['body'] ?? []),
                    ],
                    'caption' => [
                        'html' => $this->runsToHtml($newBlock->parts['caption'] ?? []),
                    ],
                ],
                'meta' => array_merge([
                    'text' => $this->diffInlineMeta($oldBlock->parts['body'] ?? [], $newBlock->parts['body'] ?? []),
                    'caption' => $this->diffInlineMeta($oldBlock->parts['caption'] ?? [], $newBlock->parts['caption'] ?? []),
                ]),
                'props' => $props,
            ],
            'hero_heading' => [
                'op' => $this->heroHeadingChanged($oldBlock, $newBlock) ? 'modified' : ($props === [] ? 'unchanged' : 'modified'),
                'kind' => 'hero_heading',
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'parts' => [
                    'pretitle' => ['html' => $this->runsToHtml($newBlock->parts['pretitle'] ?? [])],
                    'title' => ['html' => $this->runsToHtml($newBlock->parts['title'] ?? [])],
                    'subtitle' => ['html' => $this->runsToHtml($newBlock->parts['subtitle'] ?? [])],
                ],
                'meta' => array_merge([
                    'pretitle' => $this->diffInlineMeta($oldBlock->parts['pretitle'] ?? [], $newBlock->parts['pretitle'] ?? []),
                    'title' => $this->diffInlineMeta($oldBlock->parts['title'] ?? [], $newBlock->parts['title'] ?? []),
                    'subtitle' => $this->diffInlineMeta($oldBlock->parts['subtitle'] ?? [], $newBlock->parts['subtitle'] ?? []),
                ]),
                'props' => $props,
            ],
            'snippet', 'page_link', 'article_link', 'tag_link', 'image', 'file', 'image_gallery', 'embed', 'delimiter', 'unknown' => [
                'op' => $props === [] && $this->attrsSignature($oldBlock) === $this->attrsSignature($newBlock) ? 'unchanged' : 'modified',
                'kind' => $newBlock->kind,
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'parts' => $this->renderParts($newBlock),
                'items' => $newBlock->kind === 'image_gallery' ? $this->renderGalleryItems($newBlock->galleryImages) : [],
                'meta' => $this->diffStructuredBlockMeta($oldBlock, $newBlock),
                'props' => $props,
                'raw_summary' => $this->unknownRawSummary($newBlock),
            ],
            default => [
                'op' => 'modified',
                'kind' => $newBlock->kind,
                'source_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'render_key' => $newBlock->sourceKey ?? $oldBlock->sourceKey,
                'attrs' => $newBlock->attrs,
                'parts' => $this->renderParts($newBlock),
                'meta' => $this->diffStructuredBlockMeta($oldBlock, $newBlock),
                'props' => $props,
                'raw_summary' => $this->unknownRawSummary($newBlock),
            ],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function renderAddedOrRemoved(CanonicalBlock $block, string $op): array
    {
        if ($block->kind !== 'foldable') {
            return [$this->buildStandaloneRow($block, $op)];
        }

        $rows = [[
            'op' => $op,
            'kind' => 'foldable_start',
            'source_key' => $block->sourceKey,
            'render_key' => $block->sourceKey,
            'attrs' => $block->attrs,
            'body_html' => $this->runsToHtml($block->parts['title'] ?? []),
        ]];

        foreach ($block->children as $child) {
            $rows = array_merge($rows, $this->renderAddedOrRemoved($child, $op));
        }

        $rows[] = [
            'op' => $op,
            'kind' => 'foldable_end',
            'source_key' => $block->sourceKey,
            'render_key' => $block->sourceKey !== null ? $block->sourceKey . '_end' : null,
            'attrs' => [],
        ];

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStandaloneRow(CanonicalBlock $block, string $op): array
    {
        return match ($block->kind) {
            'paragraph', 'heading' => [
                'op' => $op,
                'kind' => $block->kind,
                'source_key' => $block->sourceKey,
                'render_key' => $block->sourceKey,
                'attrs' => $block->attrs,
                'body_html' => $this->runsToHtml($block->body),
            ],
            'list' => [
                'op' => $op,
                'kind' => 'list',
                'source_key' => $block->sourceKey,
                'render_key' => $block->sourceKey,
                'attrs' => $block->attrs,
                'items' => $this->renderListItems($block->listItems),
            ],
            'quote' => [
                'op' => $op,
                'kind' => 'quote',
                'source_key' => $block->sourceKey,
                'render_key' => $block->sourceKey,
                'attrs' => $block->attrs,
                'parts' => [
                    'text' => ['html' => $this->runsToHtml($block->parts['body'] ?? [])],
                    'caption' => ['html' => $this->runsToHtml($block->parts['caption'] ?? [])],
                ],
            ],
            'hero_heading' => [
                'op' => $op,
                'kind' => 'hero_heading',
                'source_key' => $block->sourceKey,
                'render_key' => $block->sourceKey,
                'attrs' => $block->attrs,
                'parts' => [
                    'pretitle' => ['html' => $this->runsToHtml($block->parts['pretitle'] ?? [])],
                    'title' => ['html' => $this->runsToHtml($block->parts['title'] ?? [])],
                    'subtitle' => ['html' => $this->runsToHtml($block->parts['subtitle'] ?? [])],
                ],
            ],
            default => [
                'op' => $op,
                'kind' => $block->kind,
                'source_key' => $block->sourceKey,
                'render_key' => $block->sourceKey,
                'attrs' => $block->attrs,
                'parts' => $this->renderParts($block),
                'items' => $block->kind === 'image_gallery' ? $this->renderGalleryItems($block->galleryImages) : [],
                'raw_summary' => $this->unknownRawSummary($block),
            ],
        };
    }

    private function blockText(CanonicalBlock $block): string
    {
        return match ($block->kind) {
            'paragraph', 'heading' => $this->extractor->runsToPlainText($block->body),
            'quote' => trim($this->extractor->runsToPlainText($block->parts['body'] ?? []) . "\n" . $this->extractor->runsToPlainText($block->parts['caption'] ?? [])),
            'list' => implode("\n", array_map($this->listItemText(...), $block->listItems)),
            'hero_heading' => trim(implode("\n", [
                $this->extractor->runsToPlainText($block->parts['pretitle'] ?? []),
                $this->extractor->runsToPlainText($block->parts['title'] ?? []),
                $this->extractor->runsToPlainText($block->parts['subtitle'] ?? []),
            ])),
            'foldable' => $this->extractor->runsToPlainText($block->parts['title'] ?? []),
            default => $this->encodeJson($block->attrs),
        };
    }

    /**
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $oldRuns
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $newRuns
     */
    private function inlineHtmlDiff(array $oldRuns, array $newRuns): string
    {
        if ($this->partsChanged($oldRuns, $newRuns) === false) {
            return $this->runsToHtml($newRuns);
        }

        $oldText = $this->extractor->runsToPlainText($oldRuns);
        $newText = $this->extractor->runsToPlainText($newRuns);

        if ($oldText === $newText) {
            return '<del>' . $this->runsToHtml($oldRuns) . '</del><ins>' . $this->runsToHtml($newRuns) . '</ins>';
        }

        return $this->inlineDiffRenderer->diff($oldText, $newText);
    }

    /**
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $oldRuns
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $newRuns
     *
     * @return array{status:string, old:string, new:string, html?:string}
     */
    private function diffInlineMeta(array $oldRuns, array $newRuns): array
    {
        $oldText = $this->extractor->runsToPlainText($oldRuns);
        $newText = $this->extractor->runsToPlainText($newRuns);

        if ($this->partsChanged($oldRuns, $newRuns) === false) {
            return [
                'status' => 'same',
                'old' => $this->runsToHtml($oldRuns),
                'new' => $this->runsToHtml($newRuns),
            ];
        }

        return [
            'status' => 'changed',
            'old' => $this->runsToHtml($oldRuns),
            'new' => $this->runsToHtml($newRuns),
            'html' => $oldText === $newText
                ? '<del>' . $this->runsToHtml($oldRuns) . '</del><ins>' . $this->runsToHtml($newRuns) . '</ins>'
                : $this->inlineDiffRenderer->diff($oldText, $newText),
        ];
    }

    /**
     * @return array<string, array{html:string}>
     */
    private function renderParts(CanonicalBlock $block): array
    {
        $parts = [];
        foreach ($block->parts as $key => $runs) {
            $parts[$key] = ['html' => $this->runsToHtml($runs)];
        }

        return $parts;
    }

    /**
     * @param list<ListItem> $items
     * @return list<array{html: string, children: list<mixed>}>
     */
    private function renderListItems(array $items): array
    {
        $rendered = [];
        foreach ($items as $item) {
            $rendered[] = [
                'html' => $this->runsToHtml($item->body),
                'children' => $this->renderListItems($item->children),
            ];
        }

        return $rendered;
    }

    /**
     * @param list<GalleryImage> $images
     * @return list<array{id: ?string, url: ?string, thumbnailUrl: ?string}>
     */
    private function renderGalleryItems(array $images): array
    {
        return array_map(static fn (GalleryImage $img) => [
            'id' => $img->id,
            'url' => $img->url,
            'thumbnailUrl' => $img->thumbnailUrl,
        ], $images);
    }

    private function unknownRawSummary(CanonicalBlock $block): ?string
    {
        if ($block->kind !== 'unknown' || !is_array($block->fallbackRaw)) {
            return null;
        }

        $summary = [
            'type' => $block->fallbackRaw['type'] ?? null,
            'data' => $block->fallbackRaw['data'] ?? null,
            'tunes' => $block->fallbackRaw['tunes'] ?? null,
        ];

        return $this->stringifyValue($summary);
    }

    /**
     * @param array<string, mixed> $oldAttrs
     * @param array<string, mixed> $newAttrs
     * @param list<string>         $skip
     *
     * @return array<string, array{status:string, old?:string, new?:string}>
     */
    private function diffAttrs(array $oldAttrs, array $newAttrs, array $skip = []): array
    {
        $meta = [];
        $keys = array_unique(array_merge(array_keys($oldAttrs), array_keys($newAttrs)));
        foreach ($keys as $key) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            $oldValue = $oldAttrs[$key] ?? null;
            $newValue = $newAttrs[$key] ?? null;
            if ($oldValue === $newValue) {
                continue;
            }
            $meta[$key] = [
                'status' => 'changed',
                'old' => $this->stringifyValue($oldValue),
                'new' => $this->stringifyValue($newValue),
            ];
        }

        return $meta;
    }

    /**
     * @return array<string, array{status:string, old?:string, new?:string}>
     */
    private function diffStructuredBlockMeta(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): array
    {
        return match ($newBlock->kind) {
            'snippet' => [
                'code' => [
                    'status' => ($oldBlock->attrs['code'] ?? null) === ($newBlock->attrs['code'] ?? null) ? 'same' : 'changed',
                    'old' => $this->stringifyScalarValue($oldBlock->attrs['code'] ?? null),
                    'new' => $this->stringifyScalarValue($newBlock->attrs['code'] ?? null),
                ],
            ],
            'page_link', 'article_link', 'tag_link' => [
                'id' => [
                    'status' => ($oldBlock->attrs['targetId'] ?? null) === ($newBlock->attrs['targetId'] ?? null) ? 'same' : 'changed',
                    'old' => $this->stringifyScalarValue($oldBlock->attrs['targetId'] ?? null),
                    'new' => $this->stringifyScalarValue($newBlock->attrs['targetId'] ?? null),
                ],
                'layout' => [
                    'status' => ($oldBlock->attrs['layout'] ?? null) === ($newBlock->attrs['layout'] ?? null) ? 'same' : 'changed',
                    'old' => $this->stringifyScalarValue($oldBlock->attrs['layout'] ?? null),
                    'new' => $this->stringifyScalarValue($newBlock->attrs['layout'] ?? null),
                ],
            ],
            'image' => [
                'fileId' => ['status' => ($oldBlock->attrs['fileId'] ?? null) === ($newBlock->attrs['fileId'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['fileId'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['fileId'] ?? null)],
                'url' => ['status' => ($oldBlock->attrs['url'] ?? null) === ($newBlock->attrs['url'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['url'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['url'] ?? null)],
                'thumbnailUrl' => ['status' => ($oldBlock->attrs['thumbnailUrl'] ?? null) === ($newBlock->attrs['thumbnailUrl'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['thumbnailUrl'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['thumbnailUrl'] ?? null)],
            ],
            'file' => [
                'fileId' => ['status' => ($oldBlock->attrs['fileId'] ?? null) === ($newBlock->attrs['fileId'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['fileId'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['fileId'] ?? null)],
                'url' => ['status' => ($oldBlock->attrs['url'] ?? null) === ($newBlock->attrs['url'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['url'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['url'] ?? null)],
            ],
            'image_gallery' => $this->diffGalleryMeta($oldBlock, $newBlock),
            'embed' => [
                'service' => ['status' => ($oldBlock->attrs['service'] ?? null) === ($newBlock->attrs['service'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['service'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['service'] ?? null)],
                'source' => ['status' => ($oldBlock->attrs['source'] ?? null) === ($newBlock->attrs['source'] ?? null) ? 'same' : 'changed', 'old' => $this->stringifyScalarValue($oldBlock->attrs['source'] ?? null), 'new' => $this->stringifyScalarValue($newBlock->attrs['source'] ?? null)],
                'caption' => $this->diffInlineMeta($oldBlock->parts['caption'] ?? [], $newBlock->parts['caption'] ?? []),
            ],
            default => $this->diffAttrs($oldBlock->attrs, $newBlock->attrs),
        };
    }

    /**
     * @return array<string, array{status:string, old?:string, new?:string, oldItem?:GalleryImage|null, newItem?:GalleryImage|null}>
     */
    private function diffGalleryMeta(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): array
    {
        $meta = [];
        $length = max(count($oldBlock->galleryImages), count($newBlock->galleryImages));
        for ($i = 0; $i < $length; $i++) {
            $oldItem = $oldBlock->galleryImages[$i] ?? null;
            $newItem = $newBlock->galleryImages[$i] ?? null;
            $meta['image[' . $i . ']'] = [
                'status' => $oldItem === $newItem ? 'same' : 'changed',
                'old' => $oldItem !== null ? $this->stringifyValue($oldItem) : '',
                'new' => $newItem !== null ? $this->stringifyValue($newItem) : '',
                'oldItem' => $oldItem,
                'newItem' => $newItem,
            ];
        }

        return $meta;
    }

    /**
     * @param list<InlineRun> $runs
     */
    private function runsToHtml(array $runs): string
    {
        $html = '';
        foreach ($runs as $run) {
            $fragment = htmlspecialchars($run->text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $fragment = str_replace("\n", '<br>', $fragment);

            foreach (array_reverse($run->marks) as $mark) {
                $fragment = $this->wrapWithMark($fragment, $mark);
            }

            $html .= $fragment;
        }

        return $html;
    }

    private function wrapWithMark(string $text, TextMark $mark): string
    {
        $href = $mark->attrs['href'] ?? '';

        return match ($mark->type) {
            'bold' => '<strong>' . $text . '</strong>',
            'italic' => '<em>' . $text . '</em>',
            'underline' => '<u>' . $text . '</u>',
            'strikethrough' => '<s>' . $text . '</s>',
            'highlight' => '<mark>' . $text . '</mark>',
            'code' => '<code>' . $text . '</code>',
            'sub' => '<sub>' . $text . '</sub>',
            'sup' => '<sup>' . $text . '</sup>',
            'link' => '<a href="' . htmlspecialchars((string) $href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $text . '</a>',
            default => $text,
        };
    }

    /**
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $oldRuns
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $newRuns
     */
    private function partsChanged(array $oldRuns, array $newRuns): bool
    {
        return serialize($oldRuns) !== serialize($newRuns);
    }

    /**
     * @param array<string, mixed> $oldAttrs
     * @param array<string, mixed> $newAttrs
     */
    private function attrsChanged(array $oldAttrs, array $newAttrs): bool
    {
        return $this->attrsSignatureArray($oldAttrs) !== $this->attrsSignatureArray($newAttrs);
    }

    private function listChanged(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): bool
    {
        return serialize($oldBlock->listItems) !== serialize($newBlock->listItems);
    }

    private function quoteChanged(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): bool
    {
        return $this->partsChanged($oldBlock->parts['body'] ?? [], $newBlock->parts['body'] ?? [])
            || $this->partsChanged($oldBlock->parts['caption'] ?? [], $newBlock->parts['caption'] ?? []);
    }

    private function heroHeadingChanged(CanonicalBlock $oldBlock, CanonicalBlock $newBlock): bool
    {
        return $this->partsChanged($oldBlock->parts['pretitle'] ?? [], $newBlock->parts['pretitle'] ?? [])
            || $this->partsChanged($oldBlock->parts['title'] ?? [], $newBlock->parts['title'] ?? [])
            || $this->partsChanged($oldBlock->parts['subtitle'] ?? [], $newBlock->parts['subtitle'] ?? []);
    }

    private function attrsSignature(CanonicalBlock $block): string
    {
        return $this->attrsSignatureArray($block->attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    private function attrsSignatureArray(array $attrs): string
    {
        ksort($attrs);

        return $this->encodeJson($attrs);
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            return $this->encodeJson($value);
        }

        return '';
    }

    private function stringifyScalarValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function listItemText(ListItem $item): string
    {
        $parts = [$this->extractor->runsToPlainText($item->body)];

        foreach ($item->children as $child) {
            $parts[] = $this->listItemText($child);
        }

        return trim(implode("\n", array_filter($parts, static fn (string $part): bool => $part !== '')));
    }

    private function textSimilarity(string $oldText, string $newText): float
    {
        if ($oldText === $newText) {
            return 1.0;
        }

        $oldTokens = $this->tokenFrequency($oldText);
        $newTokens = $this->tokenFrequency($newText);
        if ($oldTokens === [] || $newTokens === []) {
            return 0.0;
        }

        $intersection = 0;
        foreach ($oldTokens as $token => $count) {
            $intersection += min($count, $newTokens[$token] ?? 0);
        }

        $oldCount = array_sum($oldTokens);
        $newCount = array_sum($newTokens);
        if ($oldCount === 0 || $newCount === 0) {
            return 0.0;
        }

        return (2 * $intersection) / ($oldCount + $newCount);
    }

    /**
     * @return array<string, int>
     */
    private function tokenFrequency(string $text): array
    {
        $tokens = preg_split('/\W+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            return [];
        }

        $frequency = [];
        foreach ($tokens as $token) {
            $frequency[$token] = ($frequency[$token] ?? 0) + 1;
        }

        return $frequency;
    }

    /**
     * @param array<mixed, mixed> $value
     */
    private function encodeJson(array $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }
}
