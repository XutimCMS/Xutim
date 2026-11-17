<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\EventSubscriber;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\EventSubscriber\UnsupportedLocaleSubscriber;

final class UnsupportedLocaleSubscriberTest extends TestCase
{
    /**
     * @return iterable<string, array{list<string>, string, string, string}>
     */
    public static function redirectProvider(): iterable
    {
        yield 'reference locale of a single-locale site' => [['pl'], 'pl', '/en/news', '/pl/news'];
        yield 'reference locale among several' => [['pl', 'fr'], 'fr', '/en/news', '/fr/news'];
        yield 'first main locale when the reference is not served' => [['pl', 'fr'], 'en', '/de/', '/pl/'];
    }

    /**
     * @param list<string> $mainLocales
     */
    #[DataProvider('redirectProvider')]
    public function testRedirectsAnUnsupportedLocaleToOneTheSiteServes(
        array $mainLocales,
        string $referenceLocale,
        string $path,
        string $expectedPath,
    ): void {
        $event = $this->dispatch($mainLocales, $referenceLocale, $path);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($expectedPath, $response->getTargetUrl());
    }

    public function testLeavesASupportedLocaleAlone(): void
    {
        self::assertNull($this->dispatch(['pl', 'fr'], 'pl', '/fr/news')->getResponse());
    }

    public function testDoesNotRedirectWhenNoLocaleCanBeServed(): void
    {
        self::assertNull($this->dispatch([], 'en', '/en/news')->getResponse());
    }

    /**
     * @param list<string> $mainLocales
     */
    private function dispatch(array $mainLocales, string $referenceLocale, string $path): RequestEvent
    {
        $siteContext = $this->createStub(SiteContext::class);
        $siteContext->method('getMainLocales')->willReturn($mainLocales);
        $siteContext->method('getReferenceLocale')->willReturn($referenceLocale);

        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create($path),
            HttpKernelInterface::MAIN_REQUEST,
        );
        (new UnsupportedLocaleSubscriber($siteContext))->onKernelRequest($event);

        return $event;
    }
}
