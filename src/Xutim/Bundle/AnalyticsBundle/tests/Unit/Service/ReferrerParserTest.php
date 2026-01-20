<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\AnalyticsBundle\Service\ReferrerParser;

final class ReferrerParserTest extends TestCase
{
    private ReferrerParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ReferrerParser();
    }

    public function testExtractDomainReturnsNullForNullInput(): void
    {
        $this->assertNull($this->parser->extractDomain(null));
    }

    public function testExtractDomainReturnsNullForEmptyString(): void
    {
        $this->assertNull($this->parser->extractDomain(''));
    }

    public function testExtractDomainReturnsNullForInvalidUrl(): void
    {
        $this->assertNull($this->parser->extractDomain('not-a-url'));
    }

    public function testExtractDomainExtractsHost(): void
    {
        $this->assertSame(
            'example.com',
            $this->parser->extractDomain('https://example.com/path?query=1')
        );
    }

    public function testExtractDomainStripsWww(): void
    {
        $this->assertSame(
            'example.com',
            $this->parser->extractDomain('https://www.example.com/path')
        );
    }

    /**
     * @dataProvider clickIdSourcesProvider
     */
    public function testDetectSourceFromUrlDetectsClickIds(string $url, string $expectedSource): void
    {
        $this->assertSame($expectedSource, $this->parser->detectSourceFromUrl($url));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function clickIdSourcesProvider(): iterable
    {
        yield 'Facebook fbclid' => ['https://example.com/?fbclid=abc123', 'Facebook'];
        yield 'Google Ads gclid' => ['https://example.com/?gclid=xyz789', 'Google Ads'];
        yield 'Google Ads gbraid' => ['https://example.com/?gbraid=test', 'Google Ads'];
        yield 'Google Ads wbraid' => ['https://example.com/?wbraid=test', 'Google Ads'];
        yield 'Microsoft Ads msclkid' => ['https://example.com/?msclkid=ms123', 'Microsoft Ads'];
        yield 'Twitter twclid' => ['https://example.com/?twclid=tw456', 'Twitter'];
        yield 'TikTok ttclid' => ['https://example.com/?ttclid=tt789', 'TikTok'];
        yield 'LinkedIn li_fat_id' => ['https://example.com/?li_fat_id=li123', 'LinkedIn'];
        yield 'Instagram igshid' => ['https://example.com/?igshid=ig456', 'Instagram'];
        yield 'Mailchimp mc_eid' => ['https://example.com/?mc_eid=mc123', 'Mailchimp'];
        yield 'Pinterest epik' => ['https://example.com/?epik=pin123', 'Pinterest'];
        yield 'HubSpot _hsenc' => ['https://example.com/?_hsenc=hs123', 'HubSpot'];
    }

    /**
     * @dataProvider utmSourceProvider
     */
    public function testDetectSourceFromUrlDetectsUtmSource(string $url, string $expectedSource): void
    {
        $this->assertSame($expectedSource, $this->parser->detectSourceFromUrl($url));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function utmSourceProvider(): iterable
    {
        yield 'utm_source facebook' => ['https://example.com/?utm_source=facebook', 'Facebook'];
        yield 'utm_source fb' => ['https://example.com/?utm_source=fb', 'Facebook'];
        yield 'utm_source google' => ['https://example.com/?utm_source=google', 'Google'];
        yield 'utm_source twitter' => ['https://example.com/?utm_source=twitter', 'Twitter'];
        yield 'utm_source x' => ['https://example.com/?utm_source=x', 'Twitter'];
        yield 'utm_source linkedin' => ['https://example.com/?utm_source=linkedin', 'LinkedIn'];
        yield 'utm_source newsletter' => ['https://example.com/?utm_source=newsletter', 'Newsletter'];
        yield 'utm_source unknown capitalizes' => ['https://example.com/?utm_source=mycustomsource', 'Mycustomsource'];
    }

    public function testDetectSourceFromUrlReturnsNullForNoParams(): void
    {
        $this->assertNull($this->parser->detectSourceFromUrl('https://example.com/'));
    }

    public function testDetectSourceFromUrlReturnsNullForUnrelatedParams(): void
    {
        $this->assertNull($this->parser->detectSourceFromUrl('https://example.com/?foo=bar&baz=qux'));
    }

    /**
     * @dataProvider socialReferrerProvider
     */
    public function testDetectSourceFromReferrerDetectsSocialPlatforms(string $referrer, string $expectedSource): void
    {
        $this->assertSame($expectedSource, $this->parser->detectSourceFromReferrer($referrer));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function socialReferrerProvider(): iterable
    {
        yield 'Facebook' => ['https://facebook.com/post/123', 'Facebook'];
        yield 'Facebook fb.com' => ['https://fb.com/', 'Facebook'];
        yield 'Instagram' => ['https://instagram.com/p/abc', 'Instagram'];
        yield 'Twitter' => ['https://twitter.com/user/status/123', 'Twitter'];
        yield 'X.com' => ['https://x.com/user/status/123', 'Twitter'];
        yield 't.co shortlink' => ['https://t.co/abc123', 'Twitter'];
        yield 'LinkedIn' => ['https://linkedin.com/feed', 'LinkedIn'];
        yield 'YouTube' => ['https://youtube.com/watch?v=abc', 'YouTube'];
        yield 'YouTube short' => ['https://youtu.be/abc', 'YouTube'];
        yield 'Reddit' => ['https://reddit.com/r/php', 'Reddit'];
        yield 'TikTok' => ['https://tiktok.com/@user', 'TikTok'];
        yield 'Pinterest' => ['https://pinterest.com/pin/123', 'Pinterest'];
        yield 'Pinterest short' => ['https://pin.it/abc', 'Pinterest'];
        yield 'Threads' => ['https://threads.net/@user', 'Threads'];
    }

    /**
     * @dataProvider searchEngineReferrerProvider
     */
    public function testDetectSourceFromReferrerDetectsSearchEngines(string $referrer, string $expectedSource): void
    {
        $this->assertSame($expectedSource, $this->parser->detectSourceFromReferrer($referrer));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function searchEngineReferrerProvider(): iterable
    {
        yield 'Google' => ['https://www.google.com/search?q=test', 'Google'];
        yield 'Google country' => ['https://www.google.nl/search?q=test', 'Google'];
        yield 'Bing' => ['https://www.bing.com/search?q=test', 'Bing'];
        yield 'DuckDuckGo' => ['https://duckduckgo.com/?q=test', 'DuckDuckGo'];
        yield 'Yahoo' => ['https://search.yahoo.com/search?p=test', 'Yahoo'];
        yield 'Yandex' => ['https://yandex.ru/search?text=test', 'Yandex'];
        yield 'Ecosia' => ['https://www.ecosia.org/search?q=test', 'Ecosia'];
    }

    public function testDetectSourceFromReferrerReturnsNullForNull(): void
    {
        $this->assertNull($this->parser->detectSourceFromReferrer(null));
    }

    public function testDetectSourceFromReferrerReturnsDomainForUnknown(): void
    {
        $this->assertSame(
            'somewebsite.org',
            $this->parser->detectSourceFromReferrer('https://somewebsite.org/page')
        );
    }

    public function testIsExternalReturnsTrueForDifferentDomain(): void
    {
        $this->assertTrue(
            $this->parser->isExternal('https://google.com/', 'example.com')
        );
    }

    public function testIsExternalReturnsFalseForSameDomain(): void
    {
        $this->assertFalse(
            $this->parser->isExternal('https://example.com/page', 'example.com')
        );
    }

    public function testIsExternalReturnsFalseForNullReferrer(): void
    {
        $this->assertFalse(
            $this->parser->isExternal(null, 'example.com')
        );
    }

    public function testIsExternalHandlesWwwVariants(): void
    {
        $this->assertFalse(
            $this->parser->isExternal('https://www.example.com/page', 'example.com')
        );
        $this->assertFalse(
            $this->parser->isExternal('https://example.com/page', 'www.example.com')
        );
    }

    public function testIsInternalReturnsTrueForSameDomain(): void
    {
        $this->assertTrue(
            $this->parser->isInternal('https://example.com/page', 'example.com')
        );
    }

    public function testIsInternalReturnsFalseForDifferentDomain(): void
    {
        $this->assertFalse(
            $this->parser->isInternal('https://google.com/', 'example.com')
        );
    }

    public function testIsInternalReturnsFalseForNullReferrer(): void
    {
        $this->assertFalse(
            $this->parser->isInternal(null, 'example.com')
        );
    }

    public function testExtractPathReturnsNullForNull(): void
    {
        $this->assertNull($this->parser->extractPath(null));
    }

    public function testExtractPathReturnsNullForEmpty(): void
    {
        $this->assertNull($this->parser->extractPath(''));
    }

    public function testExtractPathExtractsPath(): void
    {
        $this->assertSame(
            '/some/page',
            $this->parser->extractPath('https://example.com/some/page?query=1')
        );
    }

    public function testExtractPathReturnsRootPath(): void
    {
        $this->assertSame(
            '/',
            $this->parser->extractPath('https://example.com/')
        );
    }
}
