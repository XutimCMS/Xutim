<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Transform;

use Xutim\CoreBundle\Content\Canonical\CanonicalBlock;
use Xutim\CoreBundle\Content\Canonical\CanonicalDocument;
use Xutim\CoreBundle\Content\Canonical\GalleryImage;
use Xutim\CoreBundle\Content\Canonical\ListItem;

final class EditorJsToCanonicalDocumentTransformer
{
    public function __construct(
        private readonly InlineHtmlParser $inlineHtmlParser,
    ) {
    }

    /**
     * @param array<string, mixed> $document
     */
    public function transform(array $document): CanonicalDocument
    {
        $rawBlocks = $document['blocks'] ?? [];
        if (!is_array($rawBlocks)) {
            $rawBlocks = [];
        }

        /** @var list<array<string, mixed>> $blocks */
        $blocks = array_values(array_filter($rawBlocks, is_array(...)));

        $index = 0;
        $version = $document['version'] ?? '';
        $time = $document['time'] ?? null;

        return new CanonicalDocument(
            $this->transformBlockList($blocks, $index, false),
            [
                'source' => 'editorjs',
                'sourceVersion' => is_string($version) ? $version : '',
                'createdAt' => is_int($time) ? $time : null,
            ],
        );
    }

    /**
     * @param array{id?:string, type?:string, data?:array<string, mixed>, tunes?:mixed} $block
     */
    public function transformSingleBlock(array $block): CanonicalBlock
    {
        $blocks = [$block];
        $index = 0;
        $result = $this->transformBlockList($blocks, $index, false);

        return $result[0] ?? $this->unknownBlock($block, 'single_block_transform_failed');
    }

    /**
     * @param list<array<string, mixed>> $blocks
     *
     * @return list<CanonicalBlock>
     */
    private function transformBlockList(array $blocks, int &$index, bool $stopAtFoldableEnd): array
    {
        $result = [];

        while ($index < count($blocks)) {
            $block = $blocks[$index];
            $type = is_string($block['type'] ?? null) ? $block['type'] : '';

            if ($type === 'foldableEnd') {
                if ($stopAtFoldableEnd) {
                    $index++;
                    break;
                }

                $result[] = $this->unknownBlock($block, 'orphan_foldable_end');
                $index++;
                continue;
            }

            if ($type === 'foldableStart') {
                $result[] = $this->transformFoldableBlock($blocks, $index);
                continue;
            }

            $result[] = $this->transformFlatBlock($block);
            $index++;
        }

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $blocks
     */
    private function transformFoldableBlock(array $blocks, int &$index): CanonicalBlock
    {
        $start = $blocks[$index] ?? [];
        $index++;
        $children = $this->transformBlockList($blocks, $index, true);

        $title = $this->parseInline($this->readString($start, ['data', 'title']));
        $attrs = array_merge(
            $this->extractCommonAttrs($start),
            ['open' => $this->readBool($start, ['data', 'open'])],
        );

        return new CanonicalBlock(
            kind: 'foldable',
            sourceKey: is_string($start['id'] ?? null) ? $start['id'] : null,
            attrs: $attrs,
            parts: ['title' => $title->runs],
            children: $children,
            fallbackRaw: $title->hadUnsupportedMarkup ? $start : null,
        );
    }

    /**
     * @param array<string, mixed> $block
     */
    private function transformFlatBlock(array $block): CanonicalBlock
    {
        $type = is_string($block['type'] ?? null) ? $block['type'] : '';
        $sourceKey = is_string($block['id'] ?? null) ? $block['id'] : null;
        $commonAttrs = $this->extractCommonAttrs($block);

        return match ($type) {
            'paragraph' => $this->textBlock('paragraph', $sourceKey, $commonAttrs, $this->readString($block, ['data', 'text']), $block),
            'header' => $this->headingBlock($sourceKey, $commonAttrs, $block),
            'quote' => $this->quoteBlock($sourceKey, $commonAttrs, $block),
            'list' => $this->listBlock($sourceKey, $commonAttrs, $block),
            'mainHeader' => $this->mainHeaderBlock($sourceKey, $commonAttrs, $block),
            'block' => new CanonicalBlock('snippet', $sourceKey, array_merge($commonAttrs, [
                'code' => $this->readString($block, ['data', 'code']),
            ])),
            'pageLink' => new CanonicalBlock('page_link', $sourceKey, array_merge($commonAttrs, [
                'targetId' => $this->readString($block, ['data', 'id']),
            ])),
            'articleLink' => new CanonicalBlock('article_link', $sourceKey, array_merge($commonAttrs, [
                'targetId' => $this->readString($block, ['data', 'id']),
            ])),
            'xutimTag' => new CanonicalBlock('tag_link', $sourceKey, array_merge($commonAttrs, [
                'targetId' => $this->readString($block, ['data', 'id']),
                'layout' => $this->readString($block, ['data', 'layout']),
            ])),
            'xutimImage', 'image' => new CanonicalBlock('image', $sourceKey, array_merge($commonAttrs, [
                'fileId' => $this->readString($block, ['data', 'file', 'id']),
                'url' => $this->readString($block, ['data', 'file', 'url']),
                'thumbnailUrl' => $this->readString($block, ['data', 'file', 'thumbnailUrl']),
            ])),
            'xutimFile' => new CanonicalBlock('file', $sourceKey, array_merge($commonAttrs, [
                'fileId' => $this->readString($block, ['data', 'file', 'id']),
                'url' => $this->readString($block, ['data', 'file', 'url']),
            ])),
            'imageRow', 'imagerow' => new CanonicalBlock('image_gallery', $sourceKey, array_merge($commonAttrs, [
                'imagesPerRow' => $this->readInt($block, ['data', 'imagesPerRow']),
            ]), galleryImages: $this->transformGalleryItems($block)),
            'embed' => $this->embedBlock($sourceKey, $commonAttrs, $block),
            'delimiter' => new CanonicalBlock('delimiter', $sourceKey, $commonAttrs),
            'xutimLayout' => $this->xutimLayoutBlock($sourceKey, $commonAttrs, $block),
            default => $this->unknownBlock($block, 'unsupported_block_type'),
        };
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function xutimLayoutBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $layoutCode = is_string($data['layoutCode'] ?? null) ? $data['layoutCode'] : '';
        $rawValues = is_array($data['values'] ?? null) ? $data['values'] : [];

        if ($layoutCode === '') {
            return $this->unknownBlock($block, 'xutim_layout_missing_code');
        }

        /** @var array<string, mixed> $rawValues */
        $parts = [];
        foreach ($rawValues as $fieldName => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            $parsed = $this->parseInline($value);
            $parts[$fieldName] = $parsed->runs;
        }

        return new CanonicalBlock(
            kind: 'xutim_layout',
            sourceKey: $sourceKey,
            attrs: array_merge($commonAttrs, [
                'layoutCode' => $layoutCode,
                'values' => $rawValues,
            ]),
            parts: $parts,
        );
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $raw
     */
    private function textBlock(string $kind, ?string $sourceKey, array $commonAttrs, string $text, array $raw): CanonicalBlock
    {
        $parsed = $this->parseInline($text);

        return new CanonicalBlock(
            kind: $kind,
            sourceKey: $sourceKey,
            attrs: $commonAttrs,
            body: $parsed->runs,
            fallbackRaw: $parsed->hadUnsupportedMarkup ? $raw : null,
        );
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function headingBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $parsed = $this->parseInline($this->readString($block, ['data', 'text']));
        $level = $this->readInt($block, ['data', 'level']);
        $attrs = array_merge($commonAttrs, ['level' => $level ?? 1]);

        return new CanonicalBlock('heading', $sourceKey, $attrs, $parsed->runs, fallbackRaw: $parsed->hadUnsupportedMarkup ? $block : null);
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function quoteBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $body = $this->parseInline($this->readString($block, ['data', 'text']));
        $caption = $this->parseInline($this->readString($block, ['data', 'caption']));
        $align = $this->readString($block, ['data', 'alignment']);
        $attrs = array_merge($commonAttrs, ['align' => $align !== '' ? $align : ($commonAttrs['align'] ?? null)]);

        return new CanonicalBlock(
            kind: 'quote',
            sourceKey: $sourceKey,
            attrs: $attrs,
            parts: [
                'body' => $body->runs,
                'caption' => $caption->runs,
            ],
            fallbackRaw: ($body->hadUnsupportedMarkup || $caption->hadUnsupportedMarkup) ? $block : null,
        );
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function listBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $items = [];
        $hasUnsupportedMarkup = false;
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $rawItems = is_array($data['items'] ?? null) ? $data['items'] : [];

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            /** @var array<string, mixed> $item */
            $items[] = $this->transformListItem($item, $hasUnsupportedMarkup);
        }

        return new CanonicalBlock(
            kind: 'list',
            sourceKey: $sourceKey,
            attrs: array_merge($commonAttrs, [
                'style' => $this->readString($block, ['data', 'style']),
                'start' => $this->readInt($block, ['data', 'meta', 'start']),
                'counterType' => $this->readString($block, ['data', 'meta', 'counterType']),
            ]),
            listItems: $items,
            fallbackRaw: $hasUnsupportedMarkup ? $block : null,
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function transformListItem(array $item, bool &$hasUnsupportedMarkup): ListItem
    {
        $content = $this->parseInline(is_string($item['content'] ?? null) ? $item['content'] : '');
        $hasUnsupportedMarkup = $hasUnsupportedMarkup || $content->hadUnsupportedMarkup;

        $children = [];
        $rawChildren = $item['items'] ?? null;
        if (is_array($rawChildren)) {
            foreach ($rawChildren as $child) {
                if (!is_array($child)) {
                    continue;
                }
                /** @var array<string, mixed> $child */
                $children[] = $this->transformListItem($child, $hasUnsupportedMarkup);
            }
        }

        return new ListItem($content->runs, $children);
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function mainHeaderBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $pretitle = $this->parseInline($this->readString($block, ['data', 'pretitle']));
        $title = $this->parseInline($this->readString($block, ['data', 'title']));
        $subtitle = $this->parseInline($this->readString($block, ['data', 'subtitle']));

        return new CanonicalBlock(
            kind: 'hero_heading',
            sourceKey: $sourceKey,
            attrs: $commonAttrs,
            parts: [
                'pretitle' => $pretitle->runs,
                'title' => $title->runs,
                'subtitle' => $subtitle->runs,
            ],
            fallbackRaw: ($pretitle->hadUnsupportedMarkup || $title->hadUnsupportedMarkup || $subtitle->hadUnsupportedMarkup) ? $block : null,
        );
    }

    /**
     * @param array<string, mixed> $commonAttrs
     * @param array<string, mixed> $block
     */
    private function embedBlock(?string $sourceKey, array $commonAttrs, array $block): CanonicalBlock
    {
        $caption = $this->parseInline($this->readString($block, ['data', 'caption']));

        return new CanonicalBlock(
            kind: 'embed',
            sourceKey: $sourceKey,
            attrs: array_merge($commonAttrs, [
                'service' => $this->readString($block, ['data', 'service']),
                'source' => $this->readString($block, ['data', 'source']),
                'embed' => $this->readString($block, ['data', 'embed']),
                'width' => $this->readInt($block, ['data', 'width']),
                'height' => $this->readInt($block, ['data', 'height']),
            ]),
            parts: ['caption' => $caption->runs],
            fallbackRaw: $caption->hadUnsupportedMarkup ? $block : null,
        );
    }

    /**
     * @param array<string, mixed> $block
     * @return list<GalleryImage>
     */
    private function transformGalleryItems(array $block): array
    {
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $images = $data['images'] ?? [];
        if (!is_array($images)) {
            return [];
        }

        $items = [];
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $items[] = new GalleryImage(
                id: is_string($image['id'] ?? null) ? $image['id'] : null,
                url: is_string($image['url'] ?? null) ? $image['url'] : null,
                thumbnailUrl: is_string($image['thumbnailUrl'] ?? null) ? $image['thumbnailUrl'] : null,
            );
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function extractCommonAttrs(array $block): array
    {
        $attrs = [];
        $alignment = $this->readString($block, ['tunes', 'alignment', 'alignment']);
        if ($alignment !== '') {
            $attrs['align'] = $alignment;
        }

        $anchor = $this->readString($block, ['tunes', 'xutimAnchor', 'anchor']);
        if ($anchor !== '') {
            $attrs['anchor'] = $anchor;
        }

        return $attrs;
    }

    /**
     * @param array<string, mixed> $block
     */
    private function unknownBlock(array $block, string $reason): CanonicalBlock
    {
        return new CanonicalBlock(
            kind: 'unknown',
            sourceKey: is_string($block['id'] ?? null) ? $block['id'] : null,
            attrs: [
                'sourceType' => is_string($block['type'] ?? null) ? $block['type'] : '',
                'reason' => $reason,
            ],
            fallbackRaw: $block,
        );
    }

    private function parseInline(string $html): InlineParseResult
    {
        return $this->inlineHtmlParser->parse($html);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $path
     */
    private function readString(array $data, array $path): string
    {
        $value = $this->readValue($data, $path);

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $path
     */
    private function readInt(array $data, array $path): ?int
    {
        $value = $this->readValue($data, $path);

        return is_int($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $path
     */
    private function readBool(array $data, array $path): ?bool
    {
        $value = $this->readValue($data, $path);

        return is_bool($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $path
     */
    private function readValue(array $data, array $path): mixed
    {
        $current = $data;
        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
