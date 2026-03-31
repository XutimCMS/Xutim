<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class CanonicalContentExtractorTest extends TestCase
{
    private EditorJsToCanonicalDocumentTransformer $transformer;
    private CanonicalContentExtractor $extractor;

    protected function setUp(): void
    {
        $adapter = new CanonicalEditorJsAdapter();
        $this->transformer = new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser());
        $this->extractor = new CanonicalContentExtractor($adapter);
    }

    public function test_extractors_use_canonical_document(): void
    {
        $document = $this->transformer->transform([
            'blocks' => [
                ['id' => 'h1', 'type' => 'header', 'data' => ['text' => 'Year 1', 'level' => 2], 'tunes' => []],
                ['id' => 'p1', 'type' => 'paragraph', 'data' => ['text' => 'Paragraph 1'], 'tunes' => []],
            ],
        ]);

        self::assertSame('Paragraph 1 ', $this->extractor->extractIntroduction($document));
        self::assertSame('<p>Paragraph 1</p>', $this->extractor->extractParagraphsHtml($document, 1));
        self::assertSame([
            ['header' => 'Year 1', 'paragraph' => 'Paragraph 1'],
        ], $this->extractor->extractTimelineElements($document));
    }

    public function test_extract_timeline_throws_on_invalid_sequence(): void
    {
        $document = $this->transformer->transform([
            'blocks' => [
                ['id' => 'h1', 'type' => 'header', 'data' => ['text' => 'Year 1', 'level' => 2], 'tunes' => []],
                ['id' => 'h2', 'type' => 'header', 'data' => ['text' => 'Year 2', 'level' => 2], 'tunes' => []],
            ],
        ]);

        $this->expectException(\UnexpectedValueException::class);
        $this->extractor->extractTimelineElements($document);
    }
}
