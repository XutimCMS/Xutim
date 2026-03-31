<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Content;

use UnexpectedValueException;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\Canonical\CanonicalBlock;
use Xutim\CoreBundle\Content\Canonical\CanonicalDocument;

final class CanonicalContentExtractor
{
    public function __construct(
        private readonly CanonicalEditorJsAdapter $legacyAdapter,
    ) {
    }

    public function extractIntroduction(CanonicalDocument $document): string
    {
        $text = '';
        foreach ($this->flattenBlocks($document->blocks) as $block) {
            if ($block->kind === 'paragraph') {
                $text .= $this->runsToPlainText($block->body) . ' ';
            }

            if ($block->kind === 'quote') {
                $text .= $this->runsToPlainText($block->parts['body'] ?? []) . ' ';
            }

            if ($block->kind === 'list') {
                foreach ($block->listItems as $item) {
                    $text .= $this->runsToPlainText($item->body) . ' ';
                }
            }

            if (strlen($text) > 500) {
                return $text;
            }
        }

        return $text;
    }

    public function extractParagraphsHtml(CanonicalDocument $document, int $num): string
    {
        $html = '';
        $count = 0;

        foreach ($this->flattenBlocks($document->blocks) as $block) {
            if ($block->kind !== 'paragraph') {
                continue;
            }

            $html .= sprintf('<p>%s</p>', $this->legacyAdapter->runsToHtml($block->body));
            $count++;

            if ($count === $num) {
                break;
            }
        }

        return $html;
    }

    /**
     * @return list<array{header: string, paragraph: string}>
     */
    public function extractTimelineElements(CanonicalDocument $document): array
    {
        $flatBlocks = array_values(array_filter(
            $this->flattenBlocks($document->blocks),
            fn (CanonicalBlock $block): bool => $block->kind !== 'foldable'
        ));

        $elements = [];
        for ($i = 0; $i < count($flatBlocks); $i += 2) {
            $header = $flatBlocks[$i];
            $paragraph = $flatBlocks[$i + 1] ?? null;

            if ($paragraph === null) {
                throw new UnexpectedValueException('Timeline content must contain complete heading/paragraph pairs.');
            }

            if ($header->kind !== 'heading' || $paragraph->kind !== 'paragraph') {
                throw new UnexpectedValueException('Timeline content must alternate heading and paragraph blocks.');
            }

            $elements[] = [
                'header' => $this->legacyAdapter->runsToHtml($header->body),
                'paragraph' => $this->legacyAdapter->runsToHtml($paragraph->body),
            ];
        }

        return $elements;
    }

    /**
     * @param list<CanonicalBlock> $blocks
     *
     * @return list<CanonicalBlock>
     */
    public function flattenBlocks(array $blocks): array
    {
        $flat = [];
        foreach ($blocks as $block) {
            $flat[] = $block;
            if ($block->children !== []) {
                foreach ($this->flattenBlocks($block->children) as $child) {
                    $flat[] = $child;
                }
            }
        }

        return $flat;
    }

    /**
     * @param list<\Xutim\CoreBundle\Content\Canonical\InlineRun> $runs
     */
    public function runsToPlainText(array $runs): string
    {
        $text = '';
        foreach ($runs as $run) {
            $text .= $run->text;
        }

        return $text;
    }
}
