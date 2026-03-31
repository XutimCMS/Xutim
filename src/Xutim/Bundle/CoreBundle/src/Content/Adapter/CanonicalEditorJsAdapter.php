<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content\Adapter;

use Xutim\CoreBundle\Content\Canonical\CanonicalBlock;
use Xutim\CoreBundle\Content\Canonical\CanonicalDocument;
use Xutim\CoreBundle\Content\Canonical\GalleryImage;
use Xutim\CoreBundle\Content\Canonical\InlineRun;
use Xutim\CoreBundle\Content\Canonical\ListItem;
use Xutim\CoreBundle\Content\Canonical\TextMark;

final class CanonicalEditorJsAdapter
{
    /**
     * @phpstan-return NonEmptyEditorBlock
     */
    public function toEditorJsDocument(CanonicalDocument $document): array
    {
        $time = $document->meta['createdAt'] ?? 0;
        $version = $document->meta['sourceVersion'] ?? '';

        /** @var NonEmptyEditorBlock */
        return [
            'time' => is_int($time) ? $time : 0,
            'version' => is_string($version) ? $version : '',
            'blocks' => $this->blocksToEditorJs($document->blocks),
        ];
    }

    /**
     * @param list<CanonicalBlock> $blocks
     *
     * @return list<EditorBlocksUnion>
     */
    public function toEditorJsBlocks(array $blocks): array
    {
        return $this->blocksToEditorJs($blocks);
    }

    /**
     * @param list<CanonicalBlock> $blocks
     *
     * @return list<EditorBlocksUnion>
     */
    private function blocksToEditorJs(array $blocks): array
    {
        $legacy = [];
        foreach ($blocks as $block) {
            foreach ($this->blockToEditorJs($block) as $row) {
                $legacy[] = $row;
            }
        }

        return $legacy;
    }

    /**
     * @return list<EditorBlocksUnion>
     */
    private function blockToEditorJs(CanonicalBlock $block): array
    {
        if ($block->kind === 'unknown' && is_array($block->fallbackRaw)) {
            /** @var EditorBlocksUnion $raw */
            $raw = $block->fallbackRaw;
            return [$raw];
        }

        if ($block->kind === 'foldable') {
            /** @var EditorBlocksUnion $start */
            $start = [
                'id' => $block->sourceKey ?? uniqid('foldable_', true),
                'type' => 'foldableStart',
                'data' => [
                    'title' => $this->runsToHtml($block->parts['title'] ?? []),
                    'open' => $block->attrs['open'] ?? false,
                ],
                'tunes' => $this->attrsToTunes($block->attrs),
            ];

            /** @var EditorBlocksUnion $end */
            $end = [
                'id' => ($block->sourceKey ?? uniqid('foldable_end_', true)) . '_end',
                'type' => 'foldableEnd',
                'data' => [],
                'tunes' => $this->attrsToTunes($block->attrs),
            ];

            return array_merge([$start], $this->blocksToEditorJs($block->children), [$end]);
        }

        return [$this->singleBlockToEditorJs($block)];
    }

    /**
     * @return EditorBlocksUnion
     */
    private function singleBlockToEditorJs(CanonicalBlock $block): array
    {
        return match ($block->kind) {
            'paragraph' => $this->makeBlock($block, 'paragraph', ['text' => $this->runsToHtml($block->body)]),
            'heading' => $this->makeBlock($block, 'header', [
                'text' => $this->runsToHtml($block->body),
                'level' => $block->attrs['level'] ?? 1,
            ]),
            'quote' => $this->makeBlock($block, 'quote', [
                'text' => $this->runsToHtml($block->parts['body'] ?? []),
                'caption' => $this->runsToHtml($block->parts['caption'] ?? []),
                'alignment' => $block->attrs['align'] ?? null,
            ]),
            'list' => $this->makeBlock($block, 'list', [
                'style' => $block->attrs['style'] ?? 'unordered',
                'meta' => [
                    'start' => $block->attrs['start'] ?? 1,
                    'counterType' => $block->attrs['counterType'] ?? 'numeric',
                ],
                'items' => $this->listItemsToEditorJs($block->listItems),
            ]),
            'hero_heading' => $this->makeBlock($block, 'mainHeader', [
                'pretitle' => $this->runsToHtml($block->parts['pretitle'] ?? []),
                'title' => $this->runsToHtml($block->parts['title'] ?? []),
                'subtitle' => $this->runsToHtml($block->parts['subtitle'] ?? []),
            ]),
            'snippet' => $this->makeBlock($block, 'block', ['code' => $block->attrs['code'] ?? '']),
            'page_link' => $this->makeBlock($block, 'pageLink', ['id' => $block->attrs['targetId'] ?? '']),
            'article_link' => $this->makeBlock($block, 'articleLink', ['id' => $block->attrs['targetId'] ?? '']),
            'tag_link' => $this->makeBlock($block, 'xutimTag', [
                'id' => $block->attrs['targetId'] ?? '',
                'layout' => $block->attrs['layout'] ?? '',
            ]),
            'image' => $this->makeBlock($block, 'xutimImage', [
                'file' => [
                    'id' => $block->attrs['fileId'] ?? '',
                    'url' => $block->attrs['url'] ?? '',
                    'thumbnailUrl' => $block->attrs['thumbnailUrl'] ?? '',
                ],
            ]),
            'file' => $this->makeBlock($block, 'xutimFile', [
                'file' => [
                    'id' => $block->attrs['fileId'] ?? '',
                    'url' => $block->attrs['url'] ?? '',
                ],
            ]),
            'image_gallery' => $this->makeBlock($block, 'imageRow', [
                'imagesPerRow' => $block->attrs['imagesPerRow'] ?? null,
                'images' => $this->galleryImagesToEditorJs($block->galleryImages),
            ]),
            'embed' => $this->makeBlock($block, 'embed', [
                'service' => $block->attrs['service'] ?? '',
                'source' => $block->attrs['source'] ?? '',
                'embed' => $block->attrs['embed'] ?? '',
                'width' => $block->attrs['width'] ?? null,
                'height' => $block->attrs['height'] ?? null,
                'caption' => $this->runsToHtml($block->parts['caption'] ?? []),
            ]),
            'delimiter' => $this->makeBlock($block, 'delimiter', []),
            default => $this->fallbackBlock($block),
        };
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @param list<ListItem> $items
     * @return list<array<string, mixed>>
     */
    private function listItemsToEditorJs(array $items): array
    {
        $legacy = [];
        foreach ($items as $item) {
            $legacy[] = [
                'content' => $this->runsToHtml($item->body),
                'meta' => '',
                'items' => $this->listItemsToEditorJs($item->children),
            ];
        }

        return $legacy;
    }

    /**
     * @param list<GalleryImage> $images
     * @return list<array<string, mixed>>
     */
    private function galleryImagesToEditorJs(array $images): array
    {
        return array_map(static fn (GalleryImage $img) => [
            'id' => $img->id ?? '',
            'url' => $img->url ?? '',
            'thumbnailUrl' => $img->thumbnailUrl ?? '',
        ], $images);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @phpstan-return EditorBlocksUnion
     */
    private function makeBlock(CanonicalBlock $block, string $type, array $data): array
    {
        /** @var EditorBlocksUnion */
        return [
            'id' => $block->sourceKey ?? uniqid($type . '_', true),
            'type' => $type,
            'data' => $data,
            'tunes' => $this->attrsToTunes($block->attrs),
        ];
    }

    /**
     * @phpstan-return EditorBlocksUnion
     */
    private function fallbackBlock(CanonicalBlock $block): array
    {
        if (is_array($block->fallbackRaw)) {
            /** @var EditorBlocksUnion */
            return $block->fallbackRaw;
        }

        return $this->makeBlock($block, 'paragraph', ['text' => '']);
    }

    /**
     * @param array<string, mixed> $attrs
     *
     * @return array<string, mixed>
     */
    private function attrsToTunes(array $attrs): array
    {
        $tunes = [];
        if (($attrs['align'] ?? '') !== '') {
            $tunes['alignment'] = ['alignment' => $attrs['align']];
        }
        if (($attrs['anchor'] ?? '') !== '') {
            $tunes['xutimAnchor'] = ['anchor' => $attrs['anchor']];
        }

        return $tunes;
    }

    /**
     * @param list<InlineRun> $runs
     */
    public function runsToHtml(array $runs): string
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
        return match ($mark->type) {
            'bold' => '<strong>' . $text . '</strong>',
            'italic' => '<em>' . $text . '</em>',
            'underline' => '<u>' . $text . '</u>',
            'strikethrough' => '<s>' . $text . '</s>',
            'highlight' => '<mark>' . $text . '</mark>',
            'code' => '<code>' . $text . '</code>',
            'sub' => '<sub>' . $text . '</sub>',
            'sup' => '<sup>' . $text . '</sup>',
            'link' => '<a href="' . htmlspecialchars((string) ($mark->attrs['href'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $text . '</a>',
            default => $text,
        };
    }
}
