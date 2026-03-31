<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;

final class InlineHtmlParserTest extends TestCase
{
    private InlineHtmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new InlineHtmlParser();
    }

    public function test_parse_keeps_spaces_around_marks(): void
    {
        $result = $this->parser->parse('<strong>Hello</strong> world');

        self::assertFalse($result->hadUnsupportedMarkup);
        self::assertCount(2, $result->runs);
        self::assertSame('Hello', $result->runs[0]->text);
        self::assertSame(' world', $result->runs[1]->text);
        self::assertSame('bold', $result->runs[0]->marks[0]->type);
    }

    public function test_parse_marks_unsupported_tags_but_preserves_text(): void
    {
        $result = $this->parser->parse('<span class="editorjs-marker">Hello</span> world');

        self::assertTrue($result->hadUnsupportedMarkup);
        self::assertCount(1, $result->runs);
        self::assertSame('Hello world', $result->runs[0]->text);
    }

    public function test_parse_br_into_newline_run(): void
    {
        $result = $this->parser->parse('Line one<br>Line two');

        self::assertCount(1, $result->runs);
        self::assertSame("Line one\nLine two", $result->runs[0]->text);
    }

    public function test_parse_strikethrough_mark(): void
    {
        $result = $this->parser->parse('Keep <s>removed</s> text');

        self::assertFalse($result->hadUnsupportedMarkup);
        self::assertCount(3, $result->runs);
        self::assertSame('Keep ', $result->runs[0]->text);
        self::assertSame('removed', $result->runs[1]->text);
        self::assertSame('strikethrough', $result->runs[1]->marks[0]->type);
        self::assertSame(' text', $result->runs[2]->text);
    }
}
