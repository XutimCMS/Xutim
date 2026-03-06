<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Domain\Model\TranslationLocaleAwareInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Page;

class TranslationLocaleAwareTraitTest extends TestCase
{
    public function testArticleImplementsInterface(): void
    {
        $article = new Article(null, ['en', 'fr'], new ArrayCollection(), null);

        $this->assertInstanceOf(TranslationLocaleAwareInterface::class, $article);
    }

    public function testGetTranslationLocales(): void
    {
        $article = new Article(null, ['en', 'fr', 'de'], new ArrayCollection(), null);

        $this->assertSame(['en', 'fr', 'de'], $article->getTranslationLocales());
    }

    public function testNewArticleHasAllTranslationLocalesTrue(): void
    {
        $article = new Article(null, ['en'], new ArrayCollection(), null);

        $this->assertTrue($article->hasAllTranslationLocales());
    }

    public function testIsLocaleAllowedReturnsTrueWhenAllTranslationLocales(): void
    {
        $article = new Article(null, [], new ArrayCollection(), null);

        $this->assertTrue($article->isLocaleAllowed('anything'));
    }

    public function testIsLocaleAllowedReturnsTrueForSpecificLocale(): void
    {
        $article = new Article(null, ['en', 'fr'], new ArrayCollection(), null);
        $article->changeAllTranslationLocales(false);

        $this->assertTrue($article->isLocaleAllowed('en'));
        $this->assertTrue($article->isLocaleAllowed('fr'));
    }

    public function testIsLocaleAllowedReturnsFalseForSpecificLocale(): void
    {
        $article = new Article(null, ['en'], new ArrayCollection(), null);
        $article->changeAllTranslationLocales(false);

        $this->assertFalse($article->isLocaleAllowed('de'));
        $this->assertFalse($article->isLocaleAllowed('fr'));
    }

    public function testIsLocaleAllowedWithEmptyLocalesAndNotAll(): void
    {
        $article = new Article(null, [], new ArrayCollection(), null);
        $article->changeAllTranslationLocales(false);

        $this->assertFalse($article->isLocaleAllowed('en'));
    }

    public function testChangeAllTranslationLocales(): void
    {
        $article = new Article(null, ['en'], new ArrayCollection(), null);

        $this->assertTrue($article->hasAllTranslationLocales());

        $article->changeAllTranslationLocales(false);
        $this->assertFalse($article->hasAllTranslationLocales());

        $article->changeAllTranslationLocales(true);
        $this->assertTrue($article->hasAllTranslationLocales());
    }

    public function testChangeUpdatesTranslationLocales(): void
    {
        $article = new Article(null, ['en'], new ArrayCollection(), null);
        $article->change(['en', 'fr', 'de']);

        $this->assertSame(['en', 'fr', 'de'], $article->getTranslationLocales());
    }

    public function testPageImplementsInterface(): void
    {
        $page = new Page(null, ['en', 'fr'], null, null);

        $this->assertInstanceOf(TranslationLocaleAwareInterface::class, $page);
    }

    public function testPageGetLocalesIsAlias(): void
    {
        $page = new Page(null, ['en', 'fr'], null, null);

        $this->assertSame($page->getTranslationLocales(), $page->getLocales());
    }

    public function testNewPageHasAllTranslationLocalesTrue(): void
    {
        $page = new Page(null, ['en'], null, null);

        $this->assertTrue($page->hasAllTranslationLocales());
    }

    public function testPageIsLocaleAllowedReturnsTrueWhenAll(): void
    {
        $page = new Page(null, [], null, null);

        $this->assertTrue($page->isLocaleAllowed('anything'));
    }

    public function testPageIsLocaleAllowedReturnsFalseWhenSpecific(): void
    {
        $page = new Page(null, ['en'], null, null);
        $page->changeAllTranslationLocales(false);

        $this->assertFalse($page->isLocaleAllowed('de'));
    }
}
