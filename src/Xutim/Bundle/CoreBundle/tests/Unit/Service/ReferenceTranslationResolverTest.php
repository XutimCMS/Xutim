<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\LocaleAwareInterface;
use Xutim\CoreBundle\Domain\Model\TranslatableInterface;
use Xutim\CoreBundle\Service\ReferenceTranslationResolver;

final class ReferenceTranslationResolverTest extends TestCase
{
    public function testResolveReturnsTheReferenceLocaleTranslation(): void
    {
        $pl = $this->translation('pl');
        $en = $this->translation('en');
        $de = $this->translation('de');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolve($this->translatable([$pl, $en, $de]));

        $this->assertSame($en, $result);
    }

    public function testResolveReturnsNullWhenReferenceLocaleIsMissing(): void
    {
        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolve($this->translatable([
            $this->translation('pl'),
            $this->translation('de'),
        ]));

        $this->assertNull($result);
    }

    public function testResolveReturnsNullForAnEntityWithoutTranslations(): void
    {
        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolve($this->translatable([]));

        $this->assertNull($result);
    }

    public function testResolveOrAnyPrefersTheReferenceLocale(): void
    {
        $pl = $this->translation('pl');
        $en = $this->translation('en');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveOrAny($this->translatable([$pl, $en]));

        $this->assertSame($en, $result);
    }

    public function testResolveOrAnyFallsBackToFirstAvailableWhenReferenceMissing(): void
    {
        $pl = $this->translation('pl');
        $de = $this->translation('de');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveOrAny($this->translatable([$pl, $de]));

        $this->assertSame($pl, $result);
    }

    public function testResolveOrAnyReturnsNullWhenNoTranslationsExist(): void
    {
        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveOrAny($this->translatable([]));

        $this->assertNull($result);
    }

    private function siteContext(string $referenceLocale): SiteContext
    {
        $siteContext = $this->createStub(SiteContext::class);
        $siteContext->method('getReferenceLocale')->willReturn($referenceLocale);

        return $siteContext;
    }

    private function translation(string $locale): LocaleAwareInterface
    {
        $trans = $this->createStub(LocaleAwareInterface::class);
        $trans->method('getLocale')->willReturn($locale);

        return $trans;
    }

    /**
     * @param list<LocaleAwareInterface> $translations
     */
    private function translatable(array $translations): TranslatableInterface
    {
        $entity = $this->createStub(TranslatableInterface::class);
        $entity->method('getTranslations')->willReturn($translations);

        return $entity;
    }
}
