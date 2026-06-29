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

    public function testResolveFallsBackToFirstWhenReferenceLocaleMissing(): void
    {
        $pl = $this->translation('pl');
        $de = $this->translation('de');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolve($this->translatable([$pl, $de]));

        $this->assertSame($pl, $result);
    }

    public function testResolveByLocalePrefersTheGivenLocale(): void
    {
        $pl = $this->translation('pl');
        $en = $this->translation('en');
        $de = $this->translation('de');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveByLocale($this->translatable([$pl, $en, $de]), 'de');

        $this->assertSame($de, $result);
    }

    public function testResolveByLocaleFallsBackToReferenceWhenGivenLocaleMissing(): void
    {
        $pl = $this->translation('pl');
        $en = $this->translation('en');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveByLocale($this->translatable([$pl, $en]), 'de');

        $this->assertSame($en, $result);
    }

    public function testResolveByLocaleFallsBackToFirstWhenLocaleAndReferenceBothMissing(): void
    {
        $pl = $this->translation('pl');
        $cz = $this->translation('cz');

        $resolver = new ReferenceTranslationResolver($this->siteContext('en'));
        $result = $resolver->resolveByLocale($this->translatable([$pl, $cz]), 'de');

        $this->assertSame($pl, $result);
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
