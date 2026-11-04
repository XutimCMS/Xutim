<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Xutim\CoreBundle\Content\Adapter\CanonicalEditorJsAdapter;
use Xutim\CoreBundle\Content\CanonicalContentExtractor;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;
use Xutim\CoreBundle\Content\Transform\InlineHtmlParser;
use Xutim\CoreBundle\Service\ContentFragmentsConverter;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final class ContentFragmentsConverterTest extends TestCase
{
    public function testExtractCopyrightsSkipsImageBlockWithoutFile(): void
    {
        $mediaRepo = $this->createMock(MediaRepositoryInterface::class);
        $mediaRepo->expects(self::never())->method('findById');

        $converter = new ContentFragmentsConverter(
            $this->createStub(Environment::class),
            $mediaRepo,
            new EditorJsToCanonicalDocumentTransformer(new InlineHtmlParser()),
            new CanonicalContentExtractor(new CanonicalEditorJsAdapter()),
        );

        $copyrights = $converter->extractCopyrights([
            'time' => 1,
            'version' => '2.0',
            'blocks' => [[
                'id' => 'image-block',
                'type' => 'xutimImage',
                'data' => ['file' => ['url' => '', 'id' => '', 'thumbnailUrl' => '']],
                'tunes' => ['xutimAnchor' => ['anchor' => '']],
            ]],
        ]);

        self::assertSame([], $copyrights);
    }
}
