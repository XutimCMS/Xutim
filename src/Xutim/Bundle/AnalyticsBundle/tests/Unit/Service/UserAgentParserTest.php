<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Tests\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UAParser\Parser;
use UAParser\Result\Client;
use UAParser\Result\Device;
use UAParser\Result\OperatingSystem;
use UAParser\Result\UserAgent;
use Xutim\AnalyticsBundle\Service\UserAgentParser;

final class UserAgentParserTest extends TestCase
{
    private UserAgentParser $parser;
    private Parser&MockObject $uaParser;

    protected function setUp(): void
    {
        $this->uaParser = $this->createMock(Parser::class);
        $this->parser = new UserAgentParser($this->uaParser);
    }

    private function createClient(?string $browser = null, ?string $os = null, ?string $deviceFamily = null): Client
    {
        $client = new Client('');

        $ua = new UserAgent();
        $ua->family = $browser;
        $client->ua = $ua;

        $osObj = new OperatingSystem();
        $osObj->family = $os;
        $client->os = $osObj;

        $device = new Device();
        $device->family = $deviceFamily;
        $client->device = $device;

        return $client;
    }

    public function testParseBrowserReturnsUnknownForNull(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient());

        $this->assertSame('Unknown', $this->parser->parseBrowser(null));
    }

    public function testParseBrowserReturnsUnknownForEmpty(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient());

        $this->assertSame('Unknown', $this->parser->parseBrowser(''));
    }

    public function testParseBrowserReturnsBrowserFamily(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient('Chrome'));

        $this->assertSame('Chrome', $this->parser->parseBrowser('some ua'));
    }

    public function testParseOsReturnsUnknownForNull(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient());

        $this->assertSame('Unknown', $this->parser->parseOs(null));
    }

    public function testParseOsReturnsOsFamily(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, 'Windows'));

        $this->assertSame('Windows', $this->parser->parseOs('some ua'));
    }

    public function testParseDeviceCategoryReturnsUnknownForNull(): void
    {
        $this->assertSame('Unknown', $this->parser->parseDeviceCategory(null));
    }

    public function testParseDeviceCategoryReturnsUnknownForEmpty(): void
    {
        $this->assertSame('Unknown', $this->parser->parseDeviceCategory(''));
    }

    public function testParseDeviceCategoryDetectsTabletFromDeviceFamily(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'iPad'));

        $this->assertSame('Tablet', $this->parser->parseDeviceCategory('iPad user agent'));
    }

    public function testParseDeviceCategoryDetectsMobileFromDeviceFamily(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'iPhone'));

        $this->assertSame('Mobile', $this->parser->parseDeviceCategory('iPhone user agent'));
    }

    public function testParseDeviceCategoryDetectsTabletFromUaString(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Other'));

        $this->assertSame('Tablet', $this->parser->parseDeviceCategory('Mozilla/5.0 (iPad; CPU OS 17_2)'));
    }

    public function testParseDeviceCategoryDetectsMobileFromUaString(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Other'));

        $this->assertSame('Mobile', $this->parser->parseDeviceCategory('Mozilla/5.0 (iPhone; CPU iPhone OS 17_2)'));
    }

    public function testParseDeviceCategoryDetectsAndroidMobile(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Other'));

        $this->assertSame('Mobile', $this->parser->parseDeviceCategory('Mozilla/5.0 (Linux; Android 14) Mobile'));
    }

    public function testParseDeviceCategoryDetectsAndroidTablet(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Other'));

        // Android without "Mobile" = tablet
        $this->assertSame('Tablet', $this->parser->parseDeviceCategory('Mozilla/5.0 (Linux; Android 13; SM-X710)'));
    }

    public function testParseDeviceCategoryDefaultsToDesktop(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Other'));

        $this->assertSame('Desktop', $this->parser->parseDeviceCategory('Mozilla/5.0 (Windows NT 10.0; Win64; x64)'));
    }

    public function testParseDeviceFamilyReturnsUnknownForNull(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient());

        $this->assertSame('Unknown', $this->parser->parseDeviceFamily(null));
    }

    public function testParseDeviceFamilyReturnsFamily(): void
    {
        $this->uaParser->method('parse')
            ->willReturn($this->createClient(null, null, 'Pixel 8'));

        $this->assertSame('Pixel 8', $this->parser->parseDeviceFamily('some ua'));
    }
}
