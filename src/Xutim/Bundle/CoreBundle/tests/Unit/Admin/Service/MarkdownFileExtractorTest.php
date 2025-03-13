<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Service;

use PHPUnit\Framework\TestCase;

class MarkdownFileExtractorTest extends TestCase
{
    public function testItExtractAllFiles(): void
    {
        // $fragments = [
        //     'time' => 1726280693786,
        //     'blocks' => [
        //         [
        //             'id' => '-7FHRTZf_K',
        //             'type' => 'paragraph',
        //             'data' => [
        //                 'text' => 'Something here'
        //             ]
        //         ],
        //         [
        //             'id' => '-8FHRTZf_K',
        //             'type' => 'image',
        //             'data' => [
        //                 'text' => 'Something here'
        //             ]
        //         ],
        //
        //
        //     ]
        // ];
        // $text = '
        //     # Sample Markdown
        //
        //     This is a sample Markdown document.
        //
        //     ![Image 1](/admin/json/file/show/image1.jpg)
        //
        //     [Link to File 1](/admin/json/file/show/file1.pdf)
        //
        //     Some text with a [link](https://example.com) inside.
        //
        //     ![Image 2](https://example.com/image2.png)
        //     Some text close to link![Image 2](/admin/json/file/show/image2.png)here too
        //     [](https://example.com/image3.png)
        //
        //     [Link to File 2](https://example.com/file2.docx)
        // ';
        // $expectedUrls = [
        //     'image1.jpg',
        //     'file1.pdf',
        //     'image2.png'
        // ];
        //
        //
        // $extractor = new MarkdownFileExtractor();
        // $actualUrls = $extractor->extractFiles($text);
        //
        // $this->assertCount(count($expectedUrls), $actualUrls);
        // foreach ($actualUrls as $actualUrl) {
        //     $this->assertContains($actualUrl, $expectedUrls);
        // }
    }
}
